<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'router_id', 'diff_alert_id', 'router_backup_id', 'assigned_to',
        'severity', 'status', 'summary', 'impact', 'resolution', 'acknowledged_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['acknowledged_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function diffAlert(): BelongsTo
    {
        return $this->belongsTo(DiffAlert::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'router_backup_id');
    }
}
