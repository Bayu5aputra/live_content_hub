<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationCollection;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations
     */
    public function index()
    {
        $organizations = Organization::with(['users', 'contents', 'playlists'])
            ->withCount(['users', 'contents', 'playlists'])
            ->paginate(15);

        return new OrganizationCollection($organizations);
    }

    /**
     * Store a newly created organization
     */
    public function store(StoreOrganizationRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $organization = Organization::create($validated);

        // Attach current user as admin
        $organization->users()->attach($request->user()->id, ['role' => 'admin']);

        return (new OrganizationResource($organization->load('users')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization)
    {
        $organization->load(['users', 'contents', 'playlists'])
            ->loadCount(['users', 'contents', 'playlists']);

        return new OrganizationResource($organization);
    }

    /**
     * Update the specified organization
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $validated = $request->validated();

        $organization->update($validated);

        return new OrganizationResource($organization);
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    /**
     * Add user to organization
     */
    public function addUser(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
            ]
        );

        $organization->users()->syncWithoutDetaching([
            $user->id => ['role' => $validated['role']]
        ]);

        return response()->json([
            'message' => 'User added to organization successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Remove user from organization
     */
    public function removeUser(Organization $organization, User $user)
    {
        $organization->users()->detach($user->id);

        return response()->json([
            'message' => 'User removed from organization successfully',
        ]);
    }

    /**
     * Toggle organization active status
     */
    public function toggleStatus(Organization $organization)
    {
        $organization->update([
            'is_active' => !$organization->is_active,
        ]);

        return new OrganizationResource($organization);
    }
}
