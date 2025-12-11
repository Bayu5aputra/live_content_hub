<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new organization admin user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organization_code' => 'required|string|exists:organizations,code',
        ], [
            'organization_code.required' => 'Organization code is required',
            'organization_code.exists' => 'Invalid organization code',
        ]);

        // Find organization by code
        $organization = Organization::where('code', $validated['organization_code'])->first();

        if (!$organization) {
            throw ValidationException::withMessages([
                'organization_code' => ['Invalid organization code.'],
            ]);
        }

        // Check if organization is active
        if (!$organization->is_active) {
            throw ValidationException::withMessages([
                'organization_code' => ['This organization is not active.'],
            ]);
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_super_admin' => false,
        ]);

        // Attach user to organization as admin
        $organization->users()->attach($user->id, ['role' => 'admin']);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load organizations with pivot
        $user->load('organizations');

        return response()->json([
            'message' => 'User registered successfully as organization admin',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete old tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load organizations with pivot data
        $user->load('organizations');

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'is_super_admin' => $user->is_super_admin,
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $user->load('organizations');

        return new UserResource($user);
    }
}
