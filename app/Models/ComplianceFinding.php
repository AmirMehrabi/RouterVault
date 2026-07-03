<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ComplianceFindingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceFinding extends Model
{
    /** @use HasFactory<ComplianceFindingFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id', 'router_id', 'router_backup_id', 'rule_key', 'rule_name',
        'status', 'summary', 'remediation', 'checked_at',
    ];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
