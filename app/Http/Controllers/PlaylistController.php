<?php

namespace App\Http\Controllers;

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
            ->withCount('contents')
            ->paginate(15);

        return view('organization.playlists.index', compact('organization', 'playlists'));
    }

    /**
     * Show the form for creating a new playlist
     */
    public function create(Organization $organization)
    {
        return view('organization.playlists.create', compact('organization'));
    }

    /**
     * Store a newly created playlist in storage
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
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['loop'] = $request->has('loop') ? true : false;

        Playlist::create($validated);

        return redirect()->route('organization.playlists.index', $organization)
            ->with('success', 'Playlist created successfully!');
    }

    /**
     * Display the specified playlist
     */
    public function show(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        $playlist->load(['contents' => function ($query) {
            $query->orderByPivot('order');
        }]);

        $availableContents = $organization->contents()
            ->active()
            ->whereNotIn('id', $playlist->contents->pluck('id'))
            ->ordered()
            ->get();

        return view('organization.playlists.show', compact('organization', 'playlist', 'availableContents'));
    }

    /**
     * Show the form for editing the specified playlist
     */
    public function edit(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        return view('organization.playlists.edit', compact('organization', 'playlist'));
    }

    /**
     * Update the specified playlist in storage
     */
    public function update(Request $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'loop' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['loop'] = $request->has('loop') ? true : false;

        $playlist->update($validated);

        return redirect()->route('organization.playlists.index', $organization)
            ->with('success', 'Playlist updated successfully!');
    }

    /**
     * Remove the specified playlist from storage
     */
    public function destroy(Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        $playlist->delete();

        return redirect()->route('organization.playlists.index', $organization)
            ->with('success', 'Playlist deleted successfully!');
    }

    /**
     * Add content to playlist
     */
    public function addContent(Request $request, Organization $organization, Playlist $playlist)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'content_id' => 'required|exists:contents,id',
            'order' => 'nullable|integer',
        ]);

        $content = Content::findOrFail($validated['content_id']);

        // Check if content belongs to same organization
        if ($content->organization_id !== $organization->id) {
            abort(404);
        }

        // Check if content already in playlist
        if ($playlist->contents()->where('content_id', $content->id)->exists()) {
            return back()->with('error', 'Content already in this playlist!');
        }

        $order = $validated['order'] ?? $playlist->contents()->max('playlist_content.order') + 1;

        $playlist->contents()->attach($content->id, ['order' => $order]);

        return back()->with('success', 'Content added to playlist successfully!');
    }

    /**
     * Remove content from playlist
     */
    public function removeContent(Organization $organization, Playlist $playlist, Content $content)
    {
        // Check if playlist belongs to organization
        if ($playlist->organization_id !== $organization->id) {
            abort(404);
        }

        $playlist->contents()->detach($content->id);

        return back()->with('success', 'Content removed from playlist successfully!');
    }
}
