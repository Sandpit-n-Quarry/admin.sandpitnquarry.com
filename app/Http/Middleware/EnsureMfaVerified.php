<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated, let them through (auth middleware will handle)
        if (!Auth::check()) {
            return $next($request);
        }

        // If there's a pending MFA verification, redirect to MFA page
        if ($request->session()->has('mfa_user_id')) {
            // Don't redirect if already on MFA routes
            if (!$request->routeIs('mfa.*')) {
                return redirect()->route('mfa.show');
            }
        }

        return $next($request);
    }
}
