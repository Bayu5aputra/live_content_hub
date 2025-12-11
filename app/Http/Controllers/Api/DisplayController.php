<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Playlist;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Get organization content for display
     */
    public function show(Organization $organization, Playlist $playlist = null)
    {
        if (!$organization->is_active) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        if ($playlist) {
            // Check if playlist belongs to organization
            if ($playlist->organization_id !== $organization->id) {
                return response()->json(['error' => 'Playlist not found'], 404);
            }

            $contents = $playlist->contents()
                ->where('contents.is_active', true)
                ->orderByPivot('order')
                ->get();
            $loop = $playlist->loop;
        } else {
            $contents = $organization->contents()
                ->active()
                ->ordered()
                ->get();
            $loop = true;
        }

        $data = $contents->map(function ($content) {
            return [
                'id' => $content->id,
                'type' => $content->type,
                'title' => $content->title,
                'file_url' => $content->file_url,
                'thumbnail_url' => $content->thumbnail_url,
                'duration' => $content->duration,
            ];
        });

        return response()->json([
            'organization' => [
                'name' => $organization->name,
                'slug' => $organization->slug,
            ],
            'playlist' => $playlist ? [
                'id' => $playlist->id,
                'name' => $playlist->name,
            ] : null,
            'contents' => $data,
            'loop' => $loop,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get specific playlist for display
     */
    public function showPlaylist(Organization $organization, Playlist $playlist)
    {
        return $this->show($organization, $playlist);
    }
}
