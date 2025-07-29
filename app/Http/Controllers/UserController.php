<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
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
                'status'  => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'phone_number'     => $user->phone_number,
            'avatar'           => $user->avatar ? \Illuminate\Support\Facades\Storage::url($user->avatar) : null,
            'skill_level'      => $user->skillLevel?->level_name,
            'reputation_score' => $user->reputation_score,
            'is_active'        => $user->is_active,
            'created_at'       => $user->created_at
        ]);
    }

    public function update(UpdateProfileRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized to update this profile'
            ], 403);
        }

        // Get all input data for debugging
        $allInput = $request->all();
        \Illuminate\Support\Facades\Log::debug('Update profile raw input:', $allInput);
        \Illuminate\Support\Facades\Log::debug('Request method: ' . $request->method());
        \Illuminate\Support\Facades\Log::debug('Content-Type: ' . $request->header('Content-Type'));
        \Illuminate\Support\Facades\Log::debug('Has files: ' . ($request->hasFile('avatar') ? 'yes' : 'no'));

        $updateData = $request->validated();
        \Illuminate\Support\Facades\Log::debug('Update profile validated data:', $updateData);

        // Check if we have any data to update
        if (empty($updateData)) {
            \Illuminate\Support\Facades\Log::warning('No data to update - updateData is empty');
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided for update'
            ], 400);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            \Illuminate\Support\Facades\Log::info('Processing avatar upload');
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath           = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
            \Illuminate\Support\Facades\Log::info('Avatar path set to: ' . $avatarPath);
        }

        // Handle password update
        if (!empty($updateData['password'])) {
            \Illuminate\Support\Facades\Log::info('Processing password update');
            $updateData['password'] = Hash::make($updateData['password']);
        }

        // Update directly with the updateData array instead of fill method
        $updated = $user->update($updateData);
        \Illuminate\Support\Facades\Log::info('Update result: ' . ($updated ? 'success' : 'failure'));
        \Illuminate\Support\Facades\Log::info('Updated data: ', $updateData);

        // Refresh user data
        $user->refresh();
        $user->load('skillLevel');

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'data'    => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'phone_number'     => $user->phone_number,
                'avatar'           => $user->avatar ? Storage::url($user->avatar) : null,
                'skill_level'      => $user->skillLevel?->level_name,
                'reputation_score' => $user->reputation_score,
                'is_active'        => $user->is_active,
                'created_at'       => $user->created_at
            ]
        ]);
    }

    /**
     * Get paginated list of users
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page'           => 'sometimes|integer|min:1',
            'limit'          => 'sometimes|integer|min:1|max:100',
            'search'         => 'sometimes|string|max:255',
            'skill_level_id' => 'sometimes|exists:skill_levels,id',
            'is_active'      => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
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
            'status'     => 'success',
            'data'       => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
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
                'status'  => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Check if the authenticated user is deactivating their own account
        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized to deactivate this account'
            ], 403);
        }

        $user->update(['is_active' => false]);

        return response()->json([
            'status'  => 'success',
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
                'status'  => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Check if the authenticated user is activating their own account
        $authUser = JWTAuth::parseToken()->authenticate();
        if ($authUser->id !== (int) $id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized to activate this account'
            ], 403);
        }

        $user->update(['is_active' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Account activated successfully'
        ]);
    }
}
