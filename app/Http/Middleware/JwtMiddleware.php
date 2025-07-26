<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found'
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token expired'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token invalid'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token absent'
            ], 401);
        }

        return $next($request);
    }
}
