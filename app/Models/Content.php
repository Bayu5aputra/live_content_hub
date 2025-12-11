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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_content')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('playlist_content.order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
