<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    /**
     * Redirect user to Google OAuth
     */
    // public function redirectToGoogle(): RedirectResponse|JsonResponse
    // {
    //     // return Socialite::driver('google')->stateless()->redirect();
    //     try {
    //         return Socialite::driver('google')->redirect();
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Failed to redirect to Google: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function redirectToGoogle(): RedirectResponse|JsonResponse
    {
        try {
            // Pastikan menggunakan Facade, bukan contract
            return Socialite::driver('google')->stateless()->redirect();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to redirect to Google: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $beginner = \App\Models\SkillLevel::where('level_name', 'Beginner')->first();
            $skillLevelId = $beginner ? $beginner->id : null;

            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::where('email', $googleUser->getEmail())->first();
                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar'    => $googleUser->getAvatar() ?: $user->avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name'             => $googleUser->getName(),
                        'email'            => $googleUser->getEmail(),
                        'google_id'        => $googleUser->getId(),
                        'avatar'           => $googleUser->getAvatar(),
                        'skill_level_id'   => $skillLevelId,
                        'reputation_score' => 0.00,
                        'is_active'        => true,
                    ]);
                }
            } else {
                $user->update([
                    'name'   => $googleUser->getName(),
                    'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                ]);
            }

            $user->load('skillLevel');
            $token = JWTAuth::fromUser($user);

            // Prepare user data for frontend
            $userData = [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'phone_number'     => $user->phone_number,
                'avatar'           => $user->avatar ? \Illuminate\Support\Facades\Storage::url($user->avatar) : $user->avatar,
                'skill_level'      => $user->skillLevel?->level_name,
                'reputation_score' => $user->reputation_score,
                'is_active'        => $user->is_active,
                'created_at'       => $user->created_at,
            ];
            $userJson = urlencode(json_encode($userData));
            $redirectUrl = "http://localhost:3000/auth/google/callback?token={$token}&user={$userJson}";
            return redirect($redirectUrl);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid state parameter. Please try again.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Google authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
