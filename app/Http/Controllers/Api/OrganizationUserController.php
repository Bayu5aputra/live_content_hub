<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrganizationUserController extends Controller
{
    /**
     * Get all users in organization
     */
    public function index(Request $request, Organization $organization)
    {
        // Check if user is admin of this organization
        if (!$request->user()->isAdminOf($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = $organization->users()
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->withPivot('role')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'created_at' => $user->created_at,
                ];
            });

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Add user to organization
     */
    public function store(Request $request, Organization $organization)
    {
        if (!$request->user()->isAdminOf($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        // Check if user already exists
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if already in this organization
            if ($user->organizations()->where('organization_id', $organization->id)->exists()) {
                return response()->json([
                    'message' => 'User already exists in this organization'
                ], 422);
            }
        } else {
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_super_admin' => false,
            ]);
        }

        // Attach to organization
        $organization->users()->attach($user->id, ['role' => $validated['role']]);

        return response()->json([
            'message' => 'User added successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
            ]
        ], 201);
    }

    /**
     * Update user role in organization
     */
    public function updateRole(Request $request, Organization $organization, User $user)
    {
        if (!$request->user()->isAdminOf($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,user',
        ]);

        $organization->users()->updateExistingPivot($user->id, [
            'role' => $validated['role']
        ]);

        return response()->json([
            'message' => 'User role updated successfully'
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, Organization $organization, User $user)
    {
        if (!$request->user()->isAdminOf($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Remove user from organization
     */
    public function destroy(Request $request, Organization $organization, User $user)
    {
        if (!$request->user()->isAdminOf($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent removing yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot remove yourself'], 422);
        }

        $organization->users()->detach($user->id);

        return response()->json([
            'message' => 'User removed from organization successfully'
        ]);
    }
}
