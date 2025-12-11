<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Untuk web routes, kita hanya render view
        // Token check dilakukan di JavaScript (Alpine.js)
        return $next($request);
    }
}
