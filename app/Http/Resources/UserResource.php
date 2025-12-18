<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_super_admin' => (bool) $this->is_super_admin, // Pastikan di-cast ke boolean
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

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

            'role' => $this->when(
                isset($this->pivot) && $this->pivot !== null,
                function() {
                    return $this->pivot->role ?? null;
                }
            ),
        ];
    }
}
