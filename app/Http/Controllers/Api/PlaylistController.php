<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaylistRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Http\Resources\PlaylistCollection;
use App\Http\Resources\PlaylistResource;
use App\Models\Organization;
use App\Models\Playlist;
use App\Models\Content;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    /**
     * Display a listing of playlists
     */
    public function index(Organization $organization)
    {
        $playlists = $organization->playlists()
            ->with('contents')
            ->withCount('contents')
            ->paginate(15);

        return new PlaylistCollection($playlists);
    }

    /**
     * Store a newly created playlist
     */
    public function store(StorePlaylistRequest $request, Organization $organization)
    {
        $validated = $request->validated();
        $validated['organization_id'] = $organization->id;

        $playlist = Playlist::create($validated);

        return (new PlaylistResource($playlist->load('organization')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified playlist
     */
    public function show(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->load([
            'contents' => function ($query) {
                $query->orderByPivot('order');
            },
            'organization'
        ])->loadCount('contents');

        return new PlaylistResource($playlist);
    }

    /**
     * Update the specified playlist
     */
    public function update(UpdatePlaylistRequest $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $validated = $request->validated();
        $playlist->update($validated);

        return new PlaylistResource($playlist->load('organization'));
    }

    /**
     * Remove the specified playlist
     */
    public function destroy(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->delete();

        return response()->json([
            'message' => 'Playlist deleted successfully',
        ]);
    }

    /**
     * Add content to playlist
     */
    public function addContent(Request $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $validated = $request->validate([
            'content_id' => 'required|exists:contents,id',
            'order' => 'nullable|integer',
        ]);

        $content = Content::findOrFail($validated['content_id']);

        // Check if content belongs to same organization
        if ($content->organization_id !== $organization->id) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        // Check if content already in playlist
        if ($playlist->contents()->where('content_id', $content->id)->exists()) {
            return response()->json(['message' => 'Content already in playlist'], 422);
        }

        $order = $validated['order'] ?? $playlist->contents()->max('order') + 1;

        $playlist->contents()->attach($content->id, ['order' => $order]);

        return new PlaylistResource($playlist->load(['contents', 'organization']));
    }

    /**
     * Remove content from playlist
     */
    public function removeContent(Organization $organization, Playlist $playlist, Content $content)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->contents()->detach($content->id);

        return response()->json([
            'message' => 'Content removed from playlist successfully',
        ]);
    }

    /**
     * Reorder contents in playlist
     */
    public function reorderContents(Request $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $validated = $request->validate([
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:contents,id',
            'contents.*.order' => 'required|integer',
        ]);

        foreach ($validated['contents'] as $item) {
            $playlist->contents()->updateExistingPivot($item['id'], ['order' => $item['order']]);
        }

        return new PlaylistResource($playlist->load(['contents', 'organization']));
    }

    /**
     * Toggle playlist active status
     */
    public function toggleStatus(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->update([
            'is_active' => !$playlist->is_active,
        ]);

        return new PlaylistResource($playlist);
    }
}
