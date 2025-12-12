<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperAdminController extends Controller
{
    /**
     * Get all super admins
     */
    public function index(Request $request)
    {
        // Only super admin can access
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $superAdmins = User::where('is_super_admin', true)
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $superAdmins
        ]);
    }

    /**
     * Create new super admin
     */
    public function store(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $superAdmin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_super_admin' => true,
        ]);

        return response()->json([
            'message' => 'Super admin created successfully',
            'data' => [
                'id' => $superAdmin->id,
                'name' => $superAdmin->name,
                'email' => $superAdmin->email,
            ]
        ], 201);
    }

    /**
     * Update super admin password
     */
    public function updatePassword(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$user->is_super_admin) {
            return response()->json(['message' => 'User is not a super admin'], 404);
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
     * Delete super admin
     */
    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$user->is_super_admin) {
            return response()->json(['message' => 'User is not a super admin'], 404);
        }

        // Prevent deleting yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 422);
        }

        // Check if this is the last super admin
        $superAdminCount = User::where('is_super_admin', true)->count();
        if ($superAdminCount <= 1) {
            return response()->json(['message' => 'Cannot delete the last super admin'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Super admin deleted successfully'
        ]);
    }
}
