<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBaseAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Extract base_id from route or request
        $baseId = $request->route('base')
            ?? $request->route('base_id')
            ?? $request->input('base_id');

        if (! $baseId) {
            return $next($request);
        }

        if (!$user->accessibleBaseIds()->contains($baseId)) {
            abort(403, 'Unauthorized base access.');
        }

        return $next($request);
    }
}
