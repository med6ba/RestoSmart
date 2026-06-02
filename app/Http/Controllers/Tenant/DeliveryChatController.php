<?php

namespace App\Http\Controllers\Tenant;

use App\Events\DeliveryMessageSent;
use App\Http\Controllers\Controller;
use App\Models\DeliveryMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DeliveryChatController extends Controller
{
    private const CONVERSATIONS_PER_PAGE = 5;

    private const VISIBLE_ORDER_STATUSES = ['assigned', 'out_for_delivery', 'delivered'];

    private const VISIBLE_DELIVERY_STATUSES = ['assigned', 'accepted', 'picked_up', 'out_for_delivery', 'on_the_way', 'delivered'];

    public function index(Request $request): View
    {
        return view('tenant.delivery-chat.index', $this->viewData($request));
    }

    public function show(Request $request, Order $order): View
    {
        $order = $this->authorizeConversation($request, $order);

        $this->markIncomingMessagesAsRead($order, $request->user());

        $messages = $order->deliveryMessages()
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get()
            ->map(fn (DeliveryMessage $message): array => $this->messagePayload($message, $request->user()));

        return view('tenant.delivery-chat.index', $this->viewData($request, $order, [
            'messages' => $messages,
            'recipient' => $this->recipientFor($order, $request->user()),
            'canSend' => $this->canSendMessage($order),
            'channelName' => 'delivery-chat.'.$order->id,
            'sendRoute' => route($this->routePrefix($request).'.send', [tenant('id'), $order]),
        ]));
    }

    public function send(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $order = $this->authorizeConversation($request, $order);

        if (! $this->canSendMessage($order)) {
            return $this->closedChatResponse($request);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $receiver = $this->recipientFor($order, $request->user());
        abort_unless($receiver, 403);

        $message = DeliveryMessage::query()->create([
            'order_id' => $order->id,
            'delivery_id' => $order->delivery->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $receiver->id,
            'message' => trim($data['message']),
        ])->load(['sender', 'receiver']);

        try {
            broadcast(new DeliveryMessageSent($message))->toOthers();
        } catch (Throwable $exception) {
            report($exception);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messagePayload($message, $request->user()),
            ]);
        }

        return back()->with('status', __('Message sent.'));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?Order $activeOrder = null, array $extra = []): array
    {
        return array_merge([
            'conversations' => $this->conversationQuery($request)
                ->paginate(self::CONVERSATIONS_PER_PAGE)
                ->withQueryString(),
            'activeOrder' => $activeOrder,
            'routePrefix' => $this->routePrefix($request),
            'statusLabels' => collect(Order::STATUS_FLOW)->map(fn (string $label): string => __($label))->all(),
            'messages' => collect(),
            'recipient' => null,
            'canSend' => false,
            'channelName' => null,
            'sendRoute' => null,
        ], $extra);
    }

    private function conversationQuery(Request $request): Builder
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['client', 'driver']), 403);

        $query = Order::query()
            ->with(['delivery.driver', 'user', 'latestDeliveryMessage.sender'])
            ->withCount([
                'deliveryMessages as unread_messages_count' => fn (Builder $query) => $query
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false),
            ])
            ->withMax('deliveryMessages as last_delivery_message_at', 'created_at')
            ->where('type', 'delivery')
            ->whereIn('status', self::VISIBLE_ORDER_STATUSES)
            ->whereHas('delivery', fn (Builder $query) => $query
                ->whereNotNull('driver_id')
                ->whereIn('status', self::VISIBLE_DELIVERY_STATUSES));

        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        }

        if ($user->role === 'driver') {
            $query->whereHas('delivery', fn (Builder $query) => $query->where('driver_id', $user->id));
        }

        return $query
            ->orderByRaw('COALESCE(last_delivery_message_at, orders.updated_at) desc')
            ->latest('orders.id');
    }

    private function authorizeConversation(Request $request, Order $order): Order
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['client', 'driver']), 403);

        $order->loadMissing(['delivery.driver', 'user']);

        abort_unless($this->hasChatHistoryAccess($order), 403);

        if ($user->role === 'client') {
            abort_unless((int) $order->user_id === (int) $user->id, 403);
        }

        if ($user->role === 'driver') {
            abort_unless((int) $order->delivery->driver_id === (int) $user->id, 403);
        }

        return $order;
    }

    private function hasChatHistoryAccess(Order $order): bool
    {
        return $order->type === 'delivery'
            && $order->delivery
            && $order->delivery->driver_id
            && in_array($order->status, self::VISIBLE_ORDER_STATUSES, true)
            && in_array($order->delivery->status, self::VISIBLE_DELIVERY_STATUSES, true);
    }

    private function canSendMessage(Order $order): bool
    {
        return $this->hasChatHistoryAccess($order)
            && $order->status !== 'delivered';
    }

    private function recipientFor(Order $order, User $sender): ?User
    {
        if ($sender->role === 'client') {
            return $order->delivery?->driver;
        }

        if ($sender->role === 'driver') {
            return $order->user;
        }

        return null;
    }

    private function markIncomingMessagesAsRead(Order $order, User $user): void
    {
        DeliveryMessage::query()
            ->where('order_id', $order->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(DeliveryMessage $message, User $currentUser): array
    {
        $message->loadMissing('sender');

        return [
            'id' => $message->id,
            'message_id' => $message->id,
            'order_id' => $message->order_id,
            'delivery_id' => $message->delivery_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message' => $message->message,
            'sender_name' => $message->sender?->name ?? __('Unknown user'),
            'sender_role' => $message->sender?->role ?? 'unknown',
            'created_at' => $message->created_at?->toISOString(),
            'formatted_time' => $message->created_at?->format('H:i'),
            'is_from_current_user' => $message->isFromCurrentUser($currentUser),
            'is_read' => $message->is_read,
        ];
    }

    private function closedChatResponse(Request $request): JsonResponse|RedirectResponse
    {
        $message = __('Chat is closed because this order has already been delivered.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['message' => $message]);
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'driver'
            ? 'tenant.driver.delivery-chat'
            : 'tenant.client.delivery-chat';
    }
}
