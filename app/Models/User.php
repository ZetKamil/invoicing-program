<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'is_super_admin',
    'bedrijf_id', // Transient compatibility
    'tenant_id',  // Transient compatibility
    'role',       // Transient compatibility
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'users';

    /**
     * Transient storage for backward compatibility during model creation.
     */
    protected ?string $transientTenantId = null;
    protected ?string $transientRole = null;

    /**
     * Boot model events for managing tenant_user pivot synchronization.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->transientTenantId) {
                $role = $user->transientRole ?? ($user->is_super_admin ? 'admin' : 'owner');
                $user->tenants()->syncWithoutDetaching([
                    $user->transientTenantId => [
                        'id'   => (string) Str::ulid(),
                        'role' => $role,
                    ],
                ]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships (3NF tenant_user pivot)
     | ------------------------------------------------------------------------- */

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function bedrijven(): BelongsToMany
    {
        return $this->belongsToMany(Bedrijf::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /* -------------------------------------------------------------------------
     | Backward Compatibility Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getTenantIdAttribute(): ?string
    {
        if (session()->has('current_tenant_id')) {
            return session('current_tenant_id');
        }

        return $this->tenants()->first()?->id ?? $this->transientTenantId;
    }

    public function setTenantIdAttribute(?string $value): void
    {
        $this->transientTenantId = $value;
    }

    public function getBedrijfIdAttribute(): ?string
    {
        return $this->tenant_id;
    }

    public function setBedrijfIdAttribute(?string $value): void
    {
        $this->transientTenantId = $value;
    }

    public function getTenantAttribute(): ?Tenant
    {
        if (session()->has('current_tenant_id')) {
            return Tenant::find(session('current_tenant_id'));
        }

        return $this->tenants()->first();
    }

    public function getBedrijfAttribute(): ?Tenant
    {
        return $this->tenant;
    }

    public function getRoleAttribute(): UserRole
    {
        if ($this->is_super_admin) {
            return UserRole::ADMIN;
        }

        $pivotRole = $this->tenants()->first()?->pivot?->role ?? $this->transientRole;

        return $pivotRole ? (UserRole::tryFrom($pivotRole) ?? UserRole::OWNER) : UserRole::OWNER;
    }

    public function setRoleAttribute(UserRole|string $value): void
    {
        $this->transientRole = $value instanceof UserRole ? $value->value : $value;
    }

    /* -------------------------------------------------------------------------
     | Filament v3 Multi-Tenancy Contracts
     | ------------------------------------------------------------------------- */

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->is_super_admin) {
            return Tenant::all();
        }

        return $this->tenants;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->tenants()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $role = $this->role;
        return in_array($role, [UserRole::OWNER, UserRole::ADMIN, UserRole::MANAGER, UserRole::DISPATCHER]);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canImpersonate(): bool
    {
        return $this->is_super_admin;
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->is_super_admin;
    }
}
