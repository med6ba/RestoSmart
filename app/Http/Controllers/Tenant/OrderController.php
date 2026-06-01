<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('tenant.orders.index', [
            'orders' => Order::query()
                ->with(['items', 'delivery', 'driver'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);

        return view('tenant.orders.show', [
            'order' => $order->load(['items', 'delivery.driver', 'restaurantTable']),
            'statuses' => Order::STATUS_FLOW,
        ]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return response()->json([
            'status' => $order->status,
            'label' => Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status),
            'payment_status' => $order->payment_status,
            'driver' => $order->driver?->name,
            'delivery_tracking' => $this->deliveryTracking($order->loadMissing(['delivery', 'driver'])),
            'updated_at' => $order->updated_at?->diffForHumans(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function deliveryTracking(Order $order): ?array
    {
        if ($order->type !== 'delivery' || ! $order->delivery) {
            return null;
        }

        $delivery = $order->delivery;
        $restaurant = [
            'latitude' => $delivery->restaurant_latitude ?? 33.5731,
            'longitude' => $delivery->restaurant_longitude ?? -7.5898,
        ];
        $destination = [
            'latitude' => $delivery->destination_latitude ?? ($restaurant['latitude'] + 0.05),
            'longitude' => $delivery->destination_longitude ?? ($restaurant['longitude'] + 0.05),
        ];

        $driver = $this->driverCoordinates($order, $restaurant, $destination);
        $points = $this->projectMapPoints($restaurant, $destination, $driver);

        return [
            'driver_name' => $order->driver?->name,
            'last_seen' => $delivery->last_location_at?->diffForHumans(),
            'restaurant' => $restaurant + $points['restaurant'],
            'destination' => $destination + $points['destination'],
            'driver' => $driver + $points['driver'],
        ];
    }

    /**
     * @param  array{latitude: float, longitude: float}  $restaurant
     * @param  array{latitude: float, longitude: float}  $destination
     * @return array{latitude: float, longitude: float}
     */
    private function driverCoordinates(Order $order, array $restaurant, array $destination): array
    {
        $delivery = $order->delivery;

        if ($delivery?->driver_latitude !== null && $delivery->driver_longitude !== null) {
            return [
                'latitude' => $delivery->driver_latitude,
                'longitude' => $delivery->driver_longitude,
            ];
        }

        $progress = match ($order->status) {
            'delivered' => 1,
            'out_for_delivery' => $this->simulatedDeliveryProgress($order),
            'assigned' => 0.05,
            default => 0,
        };

        return [
            'latitude' => $restaurant['latitude'] + (($destination['latitude'] - $restaurant['latitude']) * $progress),
            'longitude' => $restaurant['longitude'] + (($destination['longitude'] - $restaurant['longitude']) * $progress),
        ];
    }

    private function simulatedDeliveryProgress(Order $order): float
    {
        $startedAt = $order->delivery?->picked_up_at ?? $order->updated_at;
        $seconds = $startedAt?->diffInSeconds(now()) ?? 0;

        return min(0.92, max(0.12, $seconds / 900));
    }

    /**
     * @param  array{latitude: float, longitude: float}  $restaurant
     * @param  array{latitude: float, longitude: float}  $destination
     * @param  array{latitude: float, longitude: float}  $driver
     * @return array<string, array{x: float, y: float}>
     */
    private function projectMapPoints(array $restaurant, array $destination, array $driver): array
    {
        $latitudes = [$restaurant['latitude'], $destination['latitude'], $driver['latitude']];
        $longitudes = [$restaurant['longitude'], $destination['longitude'], $driver['longitude']];
        $minLatitude = min($latitudes);
        $maxLatitude = max($latitudes);
        $minLongitude = min($longitudes);
        $maxLongitude = max($longitudes);
        $latitudeSpan = max(0.00001, $maxLatitude - $minLatitude);
        $longitudeSpan = max(0.00001, $maxLongitude - $minLongitude);

        $project = function (array $point) use ($minLatitude, $minLongitude, $latitudeSpan, $longitudeSpan): array {
            return [
                'x' => round(12 + ((($point['longitude'] - $minLongitude) / $longitudeSpan) * 76), 2),
                'y' => round(88 - ((($point['latitude'] - $minLatitude) / $latitudeSpan) * 76), 2),
            ];
        };

        return [
            'restaurant' => $project($restaurant),
            'destination' => $project($destination),
            'driver' => $project($driver),
        ];
    }
}
