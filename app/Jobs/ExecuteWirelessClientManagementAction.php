<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WirelessClient;
use App\Models\WirelessClientManagementLog;
use App\Services\RouterOs\WirelessClientManagementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteWirelessClientManagementAction implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $wirelessClientId,
        public string $action,
        public array $payload = [],
        public ?int $userId = null
    ) {}

    public function handle(WirelessClientManagementService $wirelessClientManagementService): WirelessClientManagementLog
    {
        $wirelessClient = WirelessClient::query()
            ->withoutGlobalScopes()
            ->with(['accessPoint:id,vendor', 'router:id,vendor', 'passwordManagerCredential:id,username,password'])
            ->findOrFail($this->wirelessClientId);

        $user = $this->userId ? User::query()->find($this->userId) : null;

        return $wirelessClientManagementService->executeAction($wirelessClient, $this->action, $this->payload, $user);
    }
}
