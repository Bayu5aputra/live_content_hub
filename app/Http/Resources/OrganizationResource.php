<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'slug' => $this->slug,
            'domain' => $this->domain,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Relationships
            'users' => UserResource::collection($this->whenLoaded('users')),
            'contents' => ContentResource::collection($this->whenLoaded('contents')),
            'playlists' => PlaylistResource::collection($this->whenLoaded('playlists')),

            // Counts
            'users_count' => $this->when(
                $this->users_count !== null,
                $this->users_count
            ),
            'contents_count' => $this->when(
                $this->contents_count !== null,
                $this->contents_count
            ),
            'playlists_count' => $this->when(
                $this->playlists_count !== null,
                $this->playlists_count
            ),

            // Links
            'links' => [
                'self' => route('api.organizations.show', ['organization' => $this->slug]),
                'contents' => route('api.organizations.contents.index', ['organization' => $this->slug]),
                'playlists' => route('api.organizations.playlists.index', ['organization' => $this->slug]),
                'display' => route('display.show', ['organization' => $this->slug]),
            ],
        ];
    }
}
