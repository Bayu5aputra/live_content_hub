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

    public function hasAccessToOrganization(int $organizationId, ?string $requiredRole = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization || !$organization->pivot) {
            return false;
        }

        if ($requiredRole) {
            $roleHierarchy = ['user' => 1, 'viewer' => 2, 'editor' => 3, 'admin' => 4];
            $userRole = $organization->pivot->role ?? 'user';

            return isset($roleHierarchy[$userRole]) &&
                   isset($roleHierarchy[$requiredRole]) &&
                   $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
        }

        return true;
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

    public function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->organizations()->wherePivot('role', 'admin')->exists();
    }

    public function isAdminOf(int $organizationId): bool
    {
        return $this->hasAccessToOrganization($organizationId, 'admin');
    }

    // Check if user has 'user' role (read-only)
    public function isUserOf(int $organizationId): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        return $organization && $organization->pivot->role === 'user';
    }
}
