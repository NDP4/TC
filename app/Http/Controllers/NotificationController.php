<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notifications,
        ]);
    }

    /**
     * Mark a notification as read (only if it belongs to the user)
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read_status' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marked as read',
            'data'    => $notification,
        ]);
    }
}
