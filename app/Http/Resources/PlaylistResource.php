<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'loop' => $this->loop,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Relationships
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'contents' => ContentResource::collection($this->whenLoaded('contents')),

            // Counts
            'contents_count' => $this->when(
                $this->contents_count !== null,
                $this->contents_count
            ),

            // Calculated attributes
            'total_duration' => $this->when(
                $this->relationLoaded('contents'),
                function () {
                    return $this->getTotalDuration();
                }
            ),

            // Links
            'links' => $this->when(
                $this->organization,
                function () {
                    return [
                        'self' => route('api.organizations.playlists.show', [
                            'organization' => $this->organization->slug,
                            'playlist' => $this->id
                        ]),
                        'display' => route('display.playlist', [
                            'organization' => $this->organization->slug,
                            'playlist' => $this->id
                        ]),
                    ];
                }
            ),
        ];
    }
}
