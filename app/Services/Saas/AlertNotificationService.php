<?php

namespace App\Services\Saas;

use App\Mail\DiffAlertMail;
use App\Models\DiffAlert;
use App\Models\DiffAlertSetting;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;

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
        $settings = DiffAlertSetting::forTenant($tenant->id);
        $recipients = $settings->email_recipients ?: [$tenant->email];

        foreach (array_filter($recipients) as $recipient) {
            Mail::to($recipient)->queue(new DiffAlertMail($alert));
        }
    }
}
