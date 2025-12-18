<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    /**
     * Get dashboard statistics for Super Admin
     */
    public function stats(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_organizations'  => Organization::count(),
            'active_organizations' => Organization::where('is_active', true)->count(),
            'total_super_admins'   => User::where('is_super_admin', true)->count(),
            'total_contents'       => Content::count(),
        ]);
    }

    /**
     * Get all super admins
     */
    public function index(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => User::where('is_super_admin', true)
                ->select('id', 'name', 'email', 'created_at')
                ->orderByDesc('created_at')
                ->get()
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

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'is_super_admin' => true,
        ]);

        return response()->json([
            'message' => 'Super admin created successfully',
            'data'    => $user
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

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Delete super admin
     */
    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 422);
        }

        if (User::where('is_super_admin', true)->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last super admin'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Super admin deleted successfully']);
    }
}
