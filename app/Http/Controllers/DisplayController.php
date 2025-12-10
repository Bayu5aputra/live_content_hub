<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Playlist;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function show(Organization $organization, Playlist $playlist = null)
    {
        if (!$organization->is_active) {
            abort(404);
        }

        if ($playlist) {
            $contents = $playlist->contents()
                ->where('contents.is_active', true)
                ->get();
            $loop = $playlist->loop;
        } else {
            $contents = $organization->contents()
                ->active()
                ->ordered()
                ->get();
            $loop = true;
        }

        return view('display.show', compact('organization', 'contents', 'loop'));
    }

    public function api(Organization $organization, Playlist $playlist = null)
    {
        if (!$organization->is_active) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        if ($playlist) {
            $contents = $playlist->contents()
                ->where('contents.is_active', true)
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
            'contents' => $data,
            'loop' => $loop,
        ]);
    }
}
