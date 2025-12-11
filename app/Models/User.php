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

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function hasAccessToOrganization($organizationId, $requiredRole = null)
    {
        $org = $this->organizations()->where('organization_id', $organizationId)->first();

        if (!$org) {
            return false;
        }

        if ($requiredRole) {
            $roleHierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
            return $roleHierarchy[$org->pivot->role] >= $roleHierarchy[$requiredRole];
        }

        return true;
    }
}
