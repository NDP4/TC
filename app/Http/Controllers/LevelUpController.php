<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LevelUpRequest as LevelUpFormRequest;
use App\Http\Requests\VerifyLevelUpRequest;
use App\Models\LevelUpRequest;
use App\Models\Notification;
use App\Models\SkillLevel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LevelUpController extends Controller
{
    /**
     * Submit a level up request
     */
    public function store(LevelUpFormRequest $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check if user already has a pending request
        $existingRequest = LevelUpRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have a pending level up request'
            ], 409);
        }

        // Get target skill level
        $targetSkillLevel = SkillLevel::where('level_name', $request->target_level)->first();

        // Check if target level is higher than current level
        if ($user->skill_level_id && $targetSkillLevel->id <= $user->skill_level_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Target level must be higher than your current level'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Store uploaded documents
            $documents = [];
            $uploadedFiles = $request->file('documents', []);

            foreach ($uploadedFiles as $type => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store("level-up-documents/{$user->id}", 'public');
                    $documents[$type] = [
                        'url'           => Storage::url($path),
                        'original_name' => $file->getClientOriginalName(),
                        'size'          => $file->getSize(),
                        'mime_type'     => $file->getMimeType(),
                    ];
                }
            }

            // Create level up request
            $levelUpRequest = LevelUpRequest::create([
                'user_id'               => $user->id,
                'target_skill_level_id' => $targetSkillLevel->id,
                'documents'             => $documents,
                'notes'                 => $request->notes,
                'status'                => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Level up request submitted successfully',
                'data'    => [
                    'id'                    => $levelUpRequest->id,
                    'target_level'          => $targetSkillLevel->level_name,
                    'status'                => $levelUpRequest->status,
                    'documents_uploaded'    => count($documents),
                    'notes'                 => $levelUpRequest->notes,
                    'submitted_at'          => $levelUpRequest->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to submit level up request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's level up request details
     */
    public function show(string $id): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request = LevelUpRequest::with(['targetSkillLevel', 'verifier'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$request) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Level up request not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                  => $request->id,
                'target_level'        => $request->targetSkillLevel->level_name,
                'status'              => $request->status,
                'documents'           => $request->documents,
                'notes'               => $request->notes,
                'verification_reason' => $request->verification_reason,
                'verified_by'         => $request->verifier ? [
                    'id'   => $request->verifier->id,
                    'name' => $request->verifier->name,
                ] : null,
                'verified_at'         => $request->verified_at,
                'submitted_at'        => $request->created_at,
            ]
        ]);
    }

    /**
     * Get all level up requests (for admin/verifiers)
     */
    public function index(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        // For now, allow any authenticated user to view requests
        // In production, you might want to add role-based access control

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|string|in:pending,approved,rejected',
            'page'   => 'sometimes|integer|min:1',
            'limit'  => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = LevelUpRequest::with(['user', 'targetSkillLevel', 'verifier']);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $limit = $request->get('limit', 10);
        $requests = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data'   => $requests->items(),
            'meta'   => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
            ]
        ]);
    }

    /**
     * Verify level up request (approve/reject)
     */
    public function verify(VerifyLevelUpRequest $request, string $id): JsonResponse
    {
        $verifier = JWTAuth::parseToken()->authenticate();

        $levelUpRequest = LevelUpRequest::with(['user', 'targetSkillLevel'])
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$levelUpRequest) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Level up request not found or already processed'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Update the level up request
            $levelUpRequest->update([
                'status'              => $request->status,
                'verification_reason' => $request->reason,
                'verified_by'         => $verifier->id,
                'verified_at'         => now(),
            ]);

            // If approved, update user's skill level
            if ($request->status === 'approved') {
                $levelUpRequest->user->update([
                    'skill_level_id' => $levelUpRequest->target_skill_level_id
                ]);

                // Create success notification
                Notification::create([
                    'user_id' => $levelUpRequest->user_id,
                    'message' => "Congratulations! Your level up request to {$levelUpRequest->targetSkillLevel->level_name} has been approved.",
                ]);
            } else {
                // Create rejection notification
                Notification::create([
                    'user_id' => $levelUpRequest->user_id,
                    'message' => "Your level up request to {$levelUpRequest->targetSkillLevel->level_name} has been rejected. Reason: {$request->reason}",
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Level up request has been {$request->status} successfully",
                'data'    => [
                    'id'                  => $levelUpRequest->id,
                    'status'              => $levelUpRequest->status,
                    'verification_reason' => $levelUpRequest->verification_reason,
                    'verified_by'         => [
                        'id'   => $verifier->id,
                        'name' => $verifier->name,
                    ],
                    'verified_at'         => $levelUpRequest->verified_at,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to verify level up request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own level up requests
     */
    public function userRequests(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $requests = LevelUpRequest::with(['targetSkillLevel', 'verifier'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $requests->map(function ($request) {
                return [
                    'id'                  => $request->id,
                    'target_level'        => $request->targetSkillLevel->level_name,
                    'status'              => $request->status,
                    'documents_count'     => count($request->documents),
                    'notes'               => $request->notes,
                    'verification_reason' => $request->verification_reason,
                    'verified_by'         => $request->verifier ? [
                        'id'   => $request->verifier->id,
                        'name' => $request->verifier->name,
                    ] : null,
                    'verified_at'         => $request->verified_at,
                    'submitted_at'        => $request->created_at,
                ];
            })
        ]);
    }
}
