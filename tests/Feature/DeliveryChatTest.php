<?php

namespace Tests\Feature;

use App\Models\DeliveryMessage;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_send_message_to_assigned_driver(): void
    {
        $tenant = $this->createTenant();
        $client = $this->createUser('client', $tenant->id);
        $driver = $this->createUser('driver', $tenant->id);
        $order = $this->createDeliveryOrder($tenant, $client, $driver);

        $this->actingAs($client)
            ->get("/demo/client/delivery-chat/{$order->id}")
            ->assertOk()
            ->assertSee('delivery-chat-message')
            ->assertDontSee('Chat is closed because this order has already been delivered.');

        $this->actingAs($client)
            ->postJson("/demo/client/delivery-chat/{$order->id}/send", [
                'message' => 'I am waiting near the front door.',
            ])
            ->assertOk()
            ->assertJsonPath('message.sender_id', $client->id)
            ->assertJsonPath('message.receiver_id', $driver->id)
            ->assertJsonPath('message.message', 'I am waiting near the front door.');

        $this->assertDatabaseHas('delivery_messages', [
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'sender_id' => $client->id,
            'receiver_id' => $driver->id,
            'message' => 'I am waiting near the front door.',
        ]);
    }

    public function test_users_cannot_access_unrelated_delivery_chats(): void
    {
        $tenant = $this->createTenant();
        $client = $this->createUser('client', $tenant->id);
        $otherClient = $this->createUser('client', $tenant->id);
        $driver = $this->createUser('driver', $tenant->id);
        $otherDriver = $this->createUser('driver', $tenant->id);
        $order = $this->createDeliveryOrder($tenant, $client, $driver);

        $this->actingAs($otherClient)
            ->get("/demo/client/delivery-chat/{$order->id}")
            ->assertForbidden();

        $this->actingAs($otherDriver)
            ->get("/demo/driver/delivery-chat/{$order->id}")
            ->assertForbidden();
    }

    public function test_delivered_order_chat_keeps_history_but_locks_sending(): void
    {
        $tenant = $this->createTenant();
        $client = $this->createUser('client', $tenant->id);
        $driver = $this->createUser('driver', $tenant->id);
        $order = $this->createDeliveryOrder($tenant, $client, $driver, 'delivered', 'delivered');

        $tenant->run(function () use ($order, $driver, $client): void {
            DeliveryMessage::query()->create([
                'order_id' => $order->id,
                'delivery_id' => $order->delivery->id,
                'sender_id' => $driver->id,
                'receiver_id' => $client->id,
                'message' => 'I left the order at reception.',
            ]);
        });

        $this->actingAs($client)
            ->get("/demo/client/delivery-chat/{$order->id}")
            ->assertOk()
            ->assertSee('I left the order at reception.')
            ->assertSee('Chat Closed');

        $this->actingAs($client)
            ->postJson("/demo/client/delivery-chat/{$order->id}/send", [
                'message' => 'Thanks.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Chat is closed because this order has already been delivered.');

        $this->assertDatabaseCount('delivery_messages', 1);
    }

    public function test_admin_and_kitchen_users_do_not_see_delivery_chat_routes(): void
    {
        $tenant = $this->createTenant();
        $admin = $this->createUser('admin', $tenant->id);
        $kitchen = $this->createUser('kitchen', $tenant->id);

        $this->actingAs($admin)
            ->get('/demo/client/delivery-chat')
            ->assertForbidden();

        $this->actingAs($kitchen)
            ->get('/demo/driver/delivery-chat')
            ->assertForbidden();
    }

    private function createTenant(): Tenant
    {
        return Tenant::query()->create([
            'id' => 'demo',
            'name' => 'Demo Restaurant',
            'slug' => 'demo',
            'owner_email' => 'admin@demo.com',
            'status' => 'trial',
        ]);
    }

    private function createUser(string $role, string $tenantId): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'role' => $role,
            'status' => 'active',
            'available' => $role === 'driver',
        ]);
    }

    private function createDeliveryOrder(
        Tenant $tenant,
        User $client,
        User $driver,
        string $orderStatus = 'out_for_delivery',
        string $deliveryStatus = 'picked_up',
    ): Order {
        return $tenant->run(function () use ($client, $driver, $orderStatus, $deliveryStatus): Order {
            $order = Order::query()->create([
                'public_code' => 'RS-CHAT-'.strtoupper(fake()->bothify('####')),
                'user_id' => $client->id,
                'driver_id' => $driver->id,
                'customer_name' => $client->name,
                'customer_email' => $client->email,
                'customer_phone' => '0600000000',
                'delivery_address' => '12 Avenue Hassan II',
                'type' => 'delivery',
                'status' => $orderStatus,
                'payment_status' => $orderStatus === 'delivered' ? 'paid' : 'pending',
                'subtotal_cents' => 1000,
                'delivery_fee_cents' => 300,
                'total_cents' => 1300,
                'placed_at' => now()->subMinutes(30),
                'ready_at' => now()->subMinutes(20),
                'delivered_at' => $orderStatus === 'delivered' ? now() : null,
            ]);

            $order->delivery()->create([
                'driver_id' => $driver->id,
                'status' => $deliveryStatus,
                'route_summary' => 'Restaurant -> 12 Avenue Hassan II',
                'assigned_at' => now()->subMinutes(18),
                'picked_up_at' => in_array($deliveryStatus, ['picked_up', 'delivered'], true) ? now()->subMinutes(12) : null,
                'delivered_at' => $deliveryStatus === 'delivered' ? now() : null,
            ]);

            return $order->fresh(['delivery']);
        });
    }
}
