<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RootMiddleware
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

        if (!auth()->user()->isRoot()) {
            // abort(403, 'Unauthorized access. Root privileges required.');
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access Root module');
        }

        return $next($request);
    }
}
