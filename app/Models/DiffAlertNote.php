<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiffAlertNote extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'diff_alert_id',
        'tenant_id',
        'body',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(DiffAlert::class, 'diff_alert_id');
    }
}
