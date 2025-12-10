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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'playlist_content')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('playlist_content.order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
