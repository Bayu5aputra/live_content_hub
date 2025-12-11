<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentCollection;
use App\Http\Resources\ContentResource;
use App\Models\Organization;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Display a listing of contents
     */
    public function index(Organization $organization)
    {
        $contents = $organization->contents()
            ->with('playlists')
            ->withCount('playlists')
            ->ordered()
            ->paginate(15);

        return new ContentCollection($contents);
    }

    /**
     * Store a newly created content
     */
    public function store(StoreContentRequest $request, Organization $organization)
    {
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('contents/' . $organization->slug, 'public');
            $validated['file_path'] = $filePath;
            $validated['file_url'] = Storage::url($filePath);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->store('thumbnails/' . $organization->slug, 'public');
            $validated['thumbnail_path'] = $thumbnailPath;
            $validated['thumbnail_url'] = Storage::url($thumbnailPath);
        }

        $validated['organization_id'] = $organization->id;
        $validated['order'] = $validated['order'] ?? $organization->contents()->max('order') + 1;

        $content = Content::create($validated);

        return (new ContentResource($content->load('organization')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified content
     */
    public function show(Organization $organization, Content $content)
    {
        // Check if content belongs to organization
        if ($content->organization_id !== $organization->id) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        $content->load(['playlists', 'organization'])
            ->loadCount('playlists');

        return new ContentResource($content);
    }

    /**
     * Update the specified content
     */
    public function update(UpdateContentRequest $request, Organization $organization, Content $content)
    {
        // Check if content belongs to organization
        if ($content->organization_id !== $organization->id) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }

            $file = $request->file('file');
            $filePath = $file->store('contents/' . $organization->slug, 'public');
            $validated['file_path'] = $filePath;
            $validated['file_url'] = Storage::url($filePath);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($content->thumbnail_path) {
                Storage::disk('public')->delete($content->thumbnail_path);
            }

            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->store('thumbnails/' . $organization->slug, 'public');
            $validated['thumbnail_path'] = $thumbnailPath;
            $validated['thumbnail_url'] = Storage::url($thumbnailPath);
        }

        $content->update($validated);

        return new ContentResource($content->fresh(['organization', 'playlists']));
    }

    /**
     * Remove the specified content
     */
    public function destroy(Organization $organization, Content $content)
    {
        // Check if content belongs to organization
        if ($content->organization_id !== $organization->id) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        // Delete files
        if ($content->file_path) {
            Storage::disk('public')->delete($content->file_path);
        }
        if ($content->thumbnail_path) {
            Storage::disk('public')->delete($content->thumbnail_path);
        }

        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully',
        ]);
    }

    /**
     * Reorder contents
     */
    public function reorder(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:contents,id',
            'contents.*.order' => 'required|integer',
        ]);

        foreach ($validated['contents'] as $item) {
            Content::where('id', $item['id'])
                ->where('organization_id', $organization->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Contents reordered successfully',
        ]);
    }

    /**
     * Toggle content active status
     */
    public function toggleStatus(Organization $organization, Content $content)
    {
        // Check if content belongs to organization
        if ($content->organization_id !== $organization->id) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        $content->update([
            'is_active' => !$content->is_active,
        ]);

        return new ContentResource($content);
    }
}
