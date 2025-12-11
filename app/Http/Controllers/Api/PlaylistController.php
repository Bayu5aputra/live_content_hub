<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        return response()->json($playlists);
    }

    /**
     * Store a newly created playlist
     */
    public function store(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'loop' => 'boolean',
        ]);

        $validated['organization_id'] = $organization->id;

        $playlist = Playlist::create($validated);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist' => $playlist,
        ], 201);
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

        $playlist->load(['contents' => function ($query) {
            $query->orderByPivot('order');
        }]);

        return response()->json($playlist);
    }

    /**
     * Update the specified playlist
     */
    public function update(Request $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'loop' => 'boolean',
        ]);

        $playlist->update($validated);

        return response()->json([
            'message' => 'Playlist updated successfully',
            'playlist' => $playlist,
        ]);
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

        return response()->json([
            'message' => 'Content added to playlist successfully',
            'playlist' => $playlist->load('contents'),
        ]);
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

        return response()->json([
            'message' => 'Playlist contents reordered successfully',
            'playlist' => $playlist->load('contents'),
        ]);
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

        return response()->json([
            'message' => 'Playlist status updated successfully',
            'playlist' => $playlist,
        ]);
    }
}
