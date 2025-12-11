<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckOrganizationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $role  - Required role: 'viewer', 'editor', or 'admin'
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $request->user();

        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        // Get organization from route parameter
        $organization = $request->route('organization');

        // If organization parameter doesn't exist, allow access
        if (!$organization) {
            return $next($request);
        }

        // If organization is a string (slug), find the organization
        if (is_string($organization)) {
            $organization = Organization::where('slug', $organization)->firstOrFail();
        }

        // Check if user has access to this organization
        if (!$user->hasAccessToOrganization($organization->id, $role)) {
            // For API requests, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You do not have access to this organization'
                ], 403);
            }

            // For web requests, redirect back with error
            return redirect()->back()->with('error', 'You do not have access to this organization');
        }

        // Store user's role in request for later use
        $request->attributes->set('user_role', $user->getRoleInOrganization($organization->id));

        return $next($request);
    }
}
