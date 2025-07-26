<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Get user profile by ID
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with('skillLevel')->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'skill_level' => $user->skillLevel?->level_name,
            'reputation_score' => $user->reputation_score,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at
        ]);
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Check if the authenticated user is updating their own profile
        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this profile'
            ], 403);
        }

        $updateData = $request->only(['name', 'phone_number', 'skill_level_id']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        }

        // Handle password update
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->load('skillLevel');

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'skill_level' => $user->skillLevel?->level_name,
                'reputation_score' => $user->reputation_score,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at
            ]
        ]);
    }

    /**
     * Get paginated list of users
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'skill_level_id' => 'sometimes|exists:skill_levels,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = User::with('skillLevel');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('skill_level_id')) {
            $query->where('skill_level_id', $request->skill_level_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $limit = $request->get('limit', 10);
        $users = $query->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivate(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Check if the authenticated user is deactivating their own account
        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to deactivate this account'
            ], 403);
        }

        $user->update(['is_active' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account deactivated successfully'
        ]);
    }

    /**
     * Activate user account
     */
    public function activate(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Check if the authenticated user is activating their own account
        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to activate this account'
            ], 403);
        }

        $user->update(['is_active' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account activated successfully'
        ]);
    }
}
