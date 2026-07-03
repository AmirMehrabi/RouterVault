<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterBackupArtifact extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'router_backup_id',
        'type',
        'status',
        'disk',
        'path',
        'checksum',
        'size_bytes',
        'error_message',
        'cleanup_error',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'router_backup_id');
    }
}
