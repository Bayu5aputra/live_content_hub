<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationCollection;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with(['users'])
            ->withCount(['users', 'contents', 'playlists'])
            ->paginate(15);

        return new OrganizationCollection($organizations);
    }

    public function store(StoreOrganizationRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['code'] = strtoupper(Str::random(8));

        $organization = Organization::create([
            'name'      => $validated['name'],
            'slug'      => $validated['slug'],
            'code'      => $validated['code'],
            'domain'    => $validated['domain'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['admin_email'])) {
            $user = User::firstOrCreate(
                ['email' => $validated['admin_email']],
                [
                    'name'            => $validated['admin_name'],
                    'password'        => Hash::make($validated['admin_password']),
                    'is_super_admin'  => false,
                ]
            );

            $organization->users()->attach($user->id, ['role' => 'admin']);
        }

        return (new OrganizationResource(
            $organization->load(['users'])
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Organization $organization)
    {
        $organization->load([
            'users' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }
        ])->loadCount(['users', 'contents', 'playlists']);

        return new OrganizationResource($organization);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $validated = $request->validated();
        $organization->update($validated);

        return new OrganizationResource(
            $organization->load(['users'])
        );
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    public function toggleStatus(Organization $organization)
    {
        $organization->update([
            'is_active' => !$organization->is_active,
        ]);

        return new OrganizationResource($organization);
    }
}
