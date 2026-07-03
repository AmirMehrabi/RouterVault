<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\MaintenanceWindowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceWindow extends Model
{
    /** @use HasFactory<MaintenanceWindowFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'router_id', 'site_id', 'created_by', 'name', 'reason', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
