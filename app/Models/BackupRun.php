<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupRun extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'backup_schedule_id',
        'trigger',
        'status',
        'total_routers',
        'successful_backups',
        'failed_backups',
        'started_at',
        'finished_at',
        'error_summary',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'backup_schedule_id');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(RouterBackup::class);
    }
}
