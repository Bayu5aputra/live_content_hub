<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckOrganizationOwner
{
    /**
     * Handle an incoming request.
     *
     * Middleware to ensure user is the owner/admin of the organization
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login to continue'
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        // Get organization from route parameter
        $organization = $request->route('organization');

        if (!$organization) {
            return $next($request);
        }

        // If organization is a string (slug), find the organization
        if (is_string($organization)) {
            $organization = Organization::where('slug', $organization)->firstOrFail();
        }

        // Check if user is admin of this specific organization
        if (!$user->isAdminOf($organization->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You must be an admin of this organization'
                ], 403);
            }

            abort(403, 'You must be an admin of this organization');
        }

        return $next($request);
    }
}
