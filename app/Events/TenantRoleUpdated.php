<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantRoleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, string>  $roles
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $tenantId,
        public array $roles,
        public string $area,
        public string $type,
        public array $payload = [],
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return collect($this->roles)
            ->unique()
            ->map(fn (string $role): PrivateChannel => new PrivateChannel("tenant.{$this->tenantId}.role.{$role}"))
            ->values()
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'tenant.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'roles' => $this->roles,
            'area' => $this->area,
            'type' => $this->type,
            'payload' => $this->payload,
            'message' => $this->payload['message'] ?? __('Workspace updated.'),
        ];
    }
}
