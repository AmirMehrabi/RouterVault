<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ConfigurationBaselineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationBaseline extends Model
{
    /** @use HasFactory<ConfigurationBaselineFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'router_id', 'router_backup_id', 'approved_by', 'label', 'notes', 'approved_at'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(RouterBackup::class, 'router_backup_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
