<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token'
            ], 500);
        }

        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'skill_level' => $user->skillLevel?->level_name,
                    'reputation_score' => $user->reputation_score,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }

    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'skill_level_id' => $request->skill_level_id,
            'reputation_score' => 0.00,
            'is_active' => true,
        ]);

        // Generate token for the new user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'skill_level' => $user->skillLevel?->level_name,
                    'reputation_score' => $user->reputation_score,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout, please try again'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token could not be refreshed'
            ], 401);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function me(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $user->avatar,
                'skill_level' => $user->skillLevel?->level_name,
                'reputation_score' => $user->reputation_score,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at
            ]
        ]);
    }
}
