<?php

namespace App\Models;

use Database\Factories\PasswordManagerCredentialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasswordManagerCredential extends Model
{
    /** @use HasFactory<PasswordManagerCredentialFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'password',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class, 'password_manager_credential_id');
    }

    public function accessPoints(): HasMany
    {
        return $this->hasMany(AccessPoint::class, 'password_manager_credential_id');
    }

    public function wirelessClients(): HasMany
    {
        return $this->hasMany(WirelessClient::class, 'password_manager_credential_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (tenant()?->id) {
                $query->where('tenant_id', tenant()->id);
            } elseif (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function (PasswordManagerCredential $credential) {
            if (tenant()?->id && empty($credential->tenant_id)) {
                $credential->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($credential->tenant_id)) {
                $credential->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
