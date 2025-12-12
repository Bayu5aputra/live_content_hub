<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
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

        // Check if user is super admin
        if (!$user->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You must be a super admin to access this resource'
                ], 403);
            }

            abort(403, 'You must be a super admin to access this resource');
        }

        return $next($request);
    }
}
