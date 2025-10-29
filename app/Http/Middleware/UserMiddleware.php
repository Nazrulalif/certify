<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        if (!auth()->user()->isUser() && !auth()->user()->isRoot()) {
            // abort(403, 'Unauthorized access. User privileges required.');
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. User privileges required.');
        }

        return $next($request);
    }
}
