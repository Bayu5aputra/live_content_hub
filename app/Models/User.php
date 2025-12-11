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
     * Check if user has access to an organization with optional role check
     */
    public function hasAccessToOrganization(int $organizationId, ?string $requiredRole = null): bool
    {
        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization) {
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
        $organization = $this->organizations()->where('organizations.id', $organizationId)->first();
        return $organization ? $organization->pivot->role : null;
    }

    /**
     * Check if user is admin in any organization
     */
    public function isAdmin(): bool
    {
        return $this->organizations()->wherePivot('role', 'admin')->exists();
    }

    /**
     * Check if user is admin in specific organization
     */
    public function isAdminOf(int $organizationId): bool
    {
        return $this->hasAccessToOrganization($organizationId, 'admin');
    }

    /**
     * Check if user is super admin (has admin role in any organization)
     */
    public function isSuperAdmin(): bool
    {
        return $this->isAdmin();
    }
}
