<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RouterBackup extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'router_id',
        'backup_schedule_id',
        'backup_run_id',
        'previous_router_backup_id',
        'status',
        'changed',
        'disk',
        'path',
        'checksum',
        'size_bytes',
        'routeros_version',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'changed' => 'boolean',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'backup_schedule_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(BackupRun::class, 'backup_run_id');
    }

    public function previousBackup(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_router_backup_id');
    }

    public function diff(): HasOne
    {
        return $this->hasOne(RouterBackupDiff::class);
    }

    public function alert(): HasOne
    {
        return $this->hasOne(DiffAlert::class);
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(RouterBackupArtifact::class);
    }

    public function rscArtifact(): HasOne
    {
        return $this->hasOne(RouterBackupArtifact::class)->where('type', 'rsc');
    }
}
