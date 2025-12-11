<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'file_url' => $this->file_url,
            'thumbnail_url' => $this->thumbnail_url,
            'duration' => $this->duration,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Type helpers
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_pdf' => $this->isPdf(),

            // Relationships
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'playlists' => PlaylistResource::collection($this->whenLoaded('playlists')),

            // Pivot data when loaded through playlist
            'playlist_order' => $this->when(
                $this->pivot && isset($this->pivot->order),
                $this->pivot->order
            ),

            // Counts
            'playlists_count' => $this->when(
                $this->playlists_count !== null,
                $this->playlists_count
            ),

            // Links
            'links' => $this->when(
                $this->organization,
                function () {
                    return [
                        'self' => route('api.organizations.contents.show', [
                            'organization' => $this->organization->slug,
                            'content' => $this->id
                        ]),
                    ];
                }
            ),
        ];
    }
}
