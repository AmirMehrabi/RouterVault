<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiffAlert extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'router_id',
        'router_backup_id',
        'previous_router_backup_id',
        'router_backup_diff_id',
        'severity',
        'status',
        'summary',
        'sections',
        'matched_ignored_patterns',
        'added_lines',
        'removed_lines',
        'read_at',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'matched_ignored_patterns' => 'array',
            'read_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'router_backup_id');
    }

    public function previousBackup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'previous_router_backup_id');
    }

    public function diff(): BelongsTo
    {
        return $this->belongsTo(RouterBackupDiff::class, 'router_backup_diff_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DiffAlertNote::class);
    }
}
