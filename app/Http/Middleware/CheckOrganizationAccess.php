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
     * @param  string|null  $role  - Required role: 'user' or 'admin'
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated'], 401)
                : redirect()->route('login')->with('error', 'Please login to continue');
        }

        $organization = $request->route('organization');

        if (!$organization) {
            return $next($request);
        }

        if (is_string($organization)) {
            $organization = Organization::where('slug', $organization)->firstOrFail();
        }

        if (!$user->hasAccessToOrganization($organization->id, $role)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have access to this organization'
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'You do not have access to this organization');
        }

        $request->attributes->set('user_role', $user->getRoleInOrganization($organization->id));

        return $next($request);
    }
}
