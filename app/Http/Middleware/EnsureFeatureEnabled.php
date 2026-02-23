<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature): Response
    {
        $user = auth()->user();
        $org = $user->organization;

        if (!$org || !$org->hasFeature($feature)) {
            abort(403, 'Feature not available on your plan');
        }

        return $next($request);
    }
}
