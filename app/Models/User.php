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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
     *
     * @param int $organizationId
     * @param string|null $requiredRole
     * @return bool
     */
    public function hasAccessToOrganization($organizationId, $requiredRole = null)
    {
        $org = $this->organizations()->where('organization_id', $organizationId)->first();

        if (!$org) {
            return false;
        }

        if ($requiredRole) {
            $roleHierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
            $userRole = $org->pivot->role ?? 'viewer';

            return isset($roleHierarchy[$userRole]) &&
                   isset($roleHierarchy[$requiredRole]) &&
                   $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
        }

        return true;
    }

    /**
     * Get user's role in a specific organization
     *
     * @param int $organizationId
     * @return string|null
     */
    public function getRoleInOrganization($organizationId)
    {
        $org = $this->organizations()->where('organization_id', $organizationId)->first();
        return $org ? $org->pivot->role : null;
    }

    /**
     * Check if user is admin in any organization
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->organizations()->wherePivot('role', 'admin')->exists();
    }

    /**
     * Check if user is admin in specific organization
     *
     * @param int $organizationId
     * @return bool
     */
    public function isAdminOf($organizationId)
    {
        return $this->hasAccessToOrganization($organizationId, 'admin');
    }
}
