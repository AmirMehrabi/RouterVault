<?php

namespace App\Models;

use Database\Factories\BadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    /** @use HasFactory<BadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'icon_path',
        'display',
        'rule_class',
    ];

    public function userAwards(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['tenant_id', 'awarded_at', 'metadata'])
            ->withTimestamps();
    }
}
