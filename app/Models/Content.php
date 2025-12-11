<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'type',
        'title',
        'description',
        'file_path',
        'file_url',
        'thumbnail_path',
        'thumbnail_url',
        'duration',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relationship: Content belongs to Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relationship: Content belongs to many Playlists through pivot
     */
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_content')
            ->withPivot('order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Scope: Only active contents
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by the 'order' column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by organization
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Check if content is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if content is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if content is a PDF
     */
    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }
}
