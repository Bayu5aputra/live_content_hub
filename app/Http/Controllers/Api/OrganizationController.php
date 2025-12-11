<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->paginate(15);

        return response()->json($organizations);
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizations',
            'domain' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $organization = Organization::create($validated);

        // Attach current user as admin
        $organization->users()->attach($request->user()->id, ['role' => 'admin']);

        return response()->json([
            'message' => 'Organization created successfully',
            'organization' => $organization->load('users'),
        ], 201);
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization)
    {
        $organization->load(['users', 'contents', 'playlists']);

        return response()->json($organization);
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug,' . $organization->id,
            'domain' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $organization->update($validated);

        return response()->json([
            'message' => 'Organization updated successfully',
            'organization' => $organization,
        ]);
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
            'user' => $user,
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

        return response()->json([
            'message' => 'Organization status updated successfully',
            'organization' => $organization,
        ]);
    }
}
