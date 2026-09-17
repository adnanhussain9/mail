<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Bypass for admins, or users who are already onboarded
            if (!$user->isAdmin() && !$user->is_onboarded) {
                // If they are not already trying to access onboarding or logout, redirect them
                if (!$request->routeIs('onboarding.*') && !$request->routeIs('logout')) {
                    return redirect()->route('onboarding.index');
                }
            }
        }

        return $next($request);
    }
}
