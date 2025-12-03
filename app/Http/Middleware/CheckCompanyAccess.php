<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow super admins to access everything
        if ($user && $user->hasRole('super_admin')) {
            return $next($request);
        }

        // Check if user has a company assigned
        if ($user && !$user->company_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not assigned to any company.',
                ], 403);
            }

            abort(403, 'You are not assigned to any company.');
        }

        // Check if user's company is active
        if ($user && $user->company && !$user->company->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your company account is inactive.',
                ], 403);
            }

            abort(403, 'Your company account is inactive.');
        }

        // Check if user is active
        if ($user && !$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is inactive.',
                ], 403);
            }

            abort(403, 'Your account is inactive.');
        }

        return $next($request);
    }
}
