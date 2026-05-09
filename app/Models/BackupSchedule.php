<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'is_enabled',
        'interval_value',
        'interval_unit',
        'timezone',
        'retention_count',
        'export_mode',
        'last_run_at',
        'last_status',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function routers(): BelongsToMany
    {
        return $this->belongsToMany(Router::class)->withTimestamps();
    }

    public function runs(): HasMany
    {
        return $this->hasMany(BackupRun::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(RouterBackup::class);
    }

    public function calculateNextRun(?\DateTimeInterface $from = null): CarbonImmutable
    {
        $from = CarbonImmutable::parse($from ?? now(), $this->timezone);

        return match ($this->interval_unit) {
            'minutes' => $from->addMinutes($this->interval_value),
            'hours' => $from->addHours($this->interval_value),
            'days' => $from->addDays($this->interval_value),
            'weeks' => $from->addWeeks($this->interval_value),
            default => $from->addHours($this->interval_value),
        };
    }
}
