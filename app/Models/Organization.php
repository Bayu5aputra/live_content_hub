<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'domain',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }

            // Generate unique code if not provided
            if (empty($organization->code)) {
                $organization->code = strtoupper(Str::random(8));
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relationship: Organization has many Users through pivot
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship: Organization has many Contents
     */
    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Relationship: Organization has many Playlists
     */
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    /**
     * Scope: Only active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get admins of this organization
     */
    public function admins()
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Get editors of this organization
     */
    public function editors()
    {
        return $this->users()->wherePivot('role', 'editor');
    }

    /**
     * Get viewers of this organization
     */
    public function viewers()
    {
        return $this->users()->wherePivot('role', 'viewer');
    }
}
