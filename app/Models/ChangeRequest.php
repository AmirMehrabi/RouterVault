<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ChangeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    /** @use HasFactory<ChangeRequestFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'router_id', 'requested_by', 'approved_by', 'pre_change_backup_id',
        'status', 'title', 'reason', 'ticket_reference', 'implementation_plan', 'result',
        'approved_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function preChangeBackup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'pre_change_backup_id');
    }
}
