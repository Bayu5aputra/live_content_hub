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

    /**
     * Relationship: User belongs to many Organizations
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Check if user has access to an organization with optional role check
     */
    public function hasAccessToOrganization(int $organizationId, ?string $requiredRole = null): bool
    {
        // Super admin has access to all organizations
        if ($this->isSuperAdmin()) {
            return true;
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization) {
            return false;
        }

        if (!$organization->pivot) {
            return false;
        }

        if ($requiredRole) {
            $roleHierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
            $userRole = $organization->pivot->role ?? 'viewer';

            return isset($roleHierarchy[$userRole]) &&
                   isset($roleHierarchy[$requiredRole]) &&
                   $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
        }

        return true;
    }

    /**
     * Get user's role in a specific organization
     */
    public function getRoleInOrganization(int $organizationId): ?string
    {
        // Super admin always has admin role
        if ($this->isSuperAdmin()) {
            return 'admin';
        }

        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization || !$organization->pivot) {
            return null;
        }

        return $organization->pivot->role;
    }

    /**
     * Check if user is admin in any organization
     */
    public function isAdmin(): bool
    {
        // Super admin is always admin
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->organizations()->wherePivot('role', 'admin')->exists();
    }

    /**
     * Check if user is admin in specific organization
     */
    public function isAdminOf(int $organizationId): bool
    {
        return $this->hasAccessToOrganization($organizationId, 'admin');
    }
}
