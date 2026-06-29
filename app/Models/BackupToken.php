<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BackupToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'router_id',
        'token',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public static function generateForRouter(Router $router): self
    {
        return self::create([
            'tenant_id' => $router->tenant_id,
            'router_id' => $router->id,
            'token' => Str::random(64),
            'is_active' => true,
        ]);
    }

    public function markUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)
            ->where('is_active', true)
            ->first();
    }
}
