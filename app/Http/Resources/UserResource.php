<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'is_super_admin' => $this->is_super_admin,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'organizations' => $this->when(
                $this->relationLoaded('organizations'),
                function () {
                    return $this->organizations->map(function ($org) {
                        return [
                            'id' => $org->id,
                            'name' => $org->name,
                            'slug' => $org->slug,
                            'code' => $org->code,
                            'domain' => $org->domain,
                            'is_active' => $org->is_active,
                            'role' => $org->pivot->role ?? null,
                        ];
                    });
                }
            ),

            // Pivot data when loaded through relationship
            'role' => $this->when(
                isset($this->pivot) && $this->pivot !== null,
                function() {
                    return $this->pivot->role ?? null;
                }
            ),
        ];
    }
}
