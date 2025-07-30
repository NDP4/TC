<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array|string  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        // Check if user has one of the required roles
        $userRoles = $user->roles()->pluck('name')->toArray();
        $requiredRoles = is_array($roles) ? $roles : [$roles];
        $hasRole = false;
        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles)) {
                $hasRole = true;
                break;
            }
        }
        if (!$hasRole) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You do not have the required role'
            ], 403);
        }
        return $next($request);
    }
}
