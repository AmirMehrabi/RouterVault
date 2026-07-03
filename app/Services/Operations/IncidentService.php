<?php

namespace App\Services\Operations;

use App\Models\DiffAlert;
use App\Models\Incident;
use App\Models\RouterBackup;

class IncidentService
{
    public function forDiffAlert(DiffAlert $alert): Incident
    {
        return Incident::query()->firstOrCreate(
            ['diff_alert_id' => $alert->id],
            [
                'tenant_id' => $alert->tenant_id,
                'router_id' => $alert->router_id,
                'router_backup_id' => $alert->router_backup_id,
                'severity' => $alert->severity,
                'status' => 'detected',
                'summary' => $alert->summary,
                'impact' => 'A RouterOS configuration changed and requires operator review.',
            ]
        );
    }

    public function forFailedBackup(RouterBackup $backup): Incident
    {
        return Incident::query()->firstOrCreate(
            [
                'router_backup_id' => $backup->id,
                'diff_alert_id' => null,
            ],
            [
                'tenant_id' => $backup->tenant_id,
                'router_id' => $backup->router_id,
                'severity' => 'medium',
                'status' => 'detected',
                'summary' => ($backup->router?->name ?? 'Router').' backup failed',
                'impact' => $backup->error_message ?: 'No recoverable backup was created.',
            ]
        );
    }
}
