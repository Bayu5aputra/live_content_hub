<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * Middleware to ensure user is a super admin (has admin role in any organization)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login to continue'
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        // Check if user is admin in any organization
        if (!$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You must be an admin to access this resource'
                ], 403);
            }

            abort(403, 'You must be an admin to access this resource');
        }

        return $next($request);
    }
}
