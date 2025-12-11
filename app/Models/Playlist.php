<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'is_active',
        'loop',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'loop' => 'boolean',
    ];

    /**
     * Relationship: Playlist belongs to Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relationship: Playlist has many Contents through pivot
     */
    public function contents()
    {
        return $this->belongsToMany(Content::class, 'playlist_content')
            ->withPivot('order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Scope: Only active playlists
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by organization
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Get only active contents in this playlist
     */
    public function activeContents()
    {
        return $this->contents()->where('contents.is_active', true);
    }

    /**
     * Get the total duration of all contents in this playlist
     */
    public function getTotalDuration(): int
    {
        return $this->contents()->sum('duration');
    }

    /**
     * Get the count of contents in this playlist
     */
    public function getContentCount(): int
    {
        return $this->contents()->count();
    }

    /**
     * Attach content to playlist with order
     */
    public function attachContent(int $contentId, int $order = 0): void
    {
        if (!$this->contents()->where('content_id', $contentId)->exists()) {
            $this->contents()->attach($contentId, ['order' => $order]);
        }
    }

    /**
     * Detach content from playlist
     */
    public function detachContent(int $contentId): void
    {
        $this->contents()->detach($contentId);
    }

    /**
     * Update content order in playlist
     */
    public function updateContentOrder(int $contentId, int $order): void
    {
        $this->contents()->updateExistingPivot($contentId, ['order' => $order]);
    }
}
