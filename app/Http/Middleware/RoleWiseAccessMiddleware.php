<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleWiseAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $roles = array_slice(func_get_args(), 2);
        $user = $request->user();
        $role = $user->role;

        if (in_array($role, $roles)) {
            return $next($request);
        } else {
            return response()->json([
                'errors' => 'Unauthorized!'
            ], 403);
        }
    }
}
