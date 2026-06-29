<?php

namespace App\Services\Saas;

use App\Models\DiffAlert;
use App\Models\DiffAlertSetting;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramAlertService
{
    public function send(DiffAlert $alert, Tenant $tenant): void
    {
        $setting = DiffAlertSetting::forTenant($tenant->id);

        if (! $setting->telegram_chat_id || ! $setting->telegram_bot_token) {
            return;
        }

        $message = $this->formatMessage($alert);

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$setting->telegram_bot_token}/sendMessage",
                [
                    'chat_id' => $setting->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            );

            if (! $response->successful()) {
                Log::warning('Telegram alert failed', [
                    'alert_id' => $alert->id,
                    'tenant_id' => $tenant->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram alert error', [
                'alert_id' => $alert->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function formatMessage(DiffAlert $alert): string
    {
        $severityEmoji = match ($alert->severity) {
            'high' => '🔴',
            'medium' => '🟡',
            default => '🟢',
        };

        $routerName = $alert->router?->name ?? 'Unknown Router';
        $sections = implode(', ', $alert->sections ?? []);

        $message = "{$severityEmoji} <b>Configuration Change Detected</b>\n\n";
        $message .= "<b>Router:</b> {$routerName}\n";
        $message .= '<b>Severity:</b> '.ucfirst($alert->severity)."\n";

        if ($sections) {
            $message .= "<b>Sections:</b> {$sections}\n";
        }

        $message .= "<b>Changes:</b> {$alert->added_lines} added, {$alert->removed_lines} removed\n";
        $message .= "\n{$alert->summary}";

        return $message;
    }
}
