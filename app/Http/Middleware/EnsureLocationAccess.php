<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user->role == 'admin') {
            return $next($request);
        }

        $equipment = $request->route('equipment');
        if ($equipment) {
            if (!$user->canAccessEquipment($equipment)) {
                abort(403, 'Unauthorized location access');
            }
        }
        return $next($request);
    }
}
