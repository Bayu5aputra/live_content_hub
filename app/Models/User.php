<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * PERUBAHAN: Simplified role hierarchy
     * Hanya ada 2 role: admin dan user
     */
    public function hasAccessToOrganization(int $organizationId, ?string $requiredRole = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization || !$organization->pivot) {
            return false;
        }

        // Jika tidak ada required role, cukup cek apakah user ada di organization
        if (!$requiredRole) {
            return true;
        }

        // Role hierarchy: admin > user
        $roleHierarchy = ['user' => 1, 'admin' => 2];
        $userRole = $organization->pivot->role ?? 'user';

        return isset($roleHierarchy[$userRole]) &&
               isset($roleHierarchy[$requiredRole]) &&
               $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }

    public function getRoleInOrganization(int $organizationId): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'admin';
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization || !$organization->pivot) {
            return null;
        }

        return $organization->pivot->role;
    }

    public function isAdminOf(int $organizationId): bool
    {
        return $this->hasAccessToOrganization($organizationId, 'admin');
    }

    /**
     * BARU: Check if user is read-only user
     */
    public function isUserOf(int $organizationId): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        return $organization && $organization->pivot->role === 'user';
    }
}
