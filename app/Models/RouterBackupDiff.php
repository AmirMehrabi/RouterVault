<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterBackupDiff extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_backup_id',
        'previous_router_backup_id',
        'added_lines',
        'removed_lines',
        'unified_diff',
        'hunks',
    ];

    protected function casts(): array
    {
        return [
            'hunks' => 'array',
        ];
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'router_backup_id');
    }

    public function previousBackup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'previous_router_backup_id');
    }
}
