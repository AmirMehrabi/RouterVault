<?php

namespace App\Services\Saas;

use App\Models\DiffAlert;
use App\Models\Tenant;

class AlertNotificationService
{
    public function __construct(protected TelegramAlertService $telegramService) {}

    public function send(DiffAlert $alert, Tenant $tenant): void
    {
        $channels = $tenant->alert_channels;

        $this->createInAppNotification($alert);

        if (in_array('email', $channels)) {
            $this->sendEmail($alert, $tenant);
        }

        if (in_array('telegram', $channels)) {
            $this->telegramService->send($alert, $tenant);
        }
    }

    protected function createInAppNotification(DiffAlert $alert): void
    {
        // In-app notification is already created via DiffAlert model
        // This method can be extended to create additional notification records
    }

    protected function sendEmail(DiffAlert $alert, Tenant $tenant): void
    {
        // Email notification logic
        // This would use Laravel's Mail facade with a Mailable class
        // For MVP, we'll log this action
        \Log::info('Alert email sent', [
            'alert_id' => $alert->id,
            'tenant_id' => $tenant->id,
            'router_name' => $alert->router?->name,
            'severity' => $alert->severity,
        ]);
    }
}
