<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();
        $user->loadMissing('detail');
        $detail = $user->detail;
        $name = $detail?->name ?: '';
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->user_id,
                'name' => $name,
                'email' => $detail?->email ?? '',
                'phone' => $detail?->contact ?? '',
                'address' => $detail?->address ?? '',
                'avatar' => $this->generateAvatar($name ?: ($detail?->email ?? 'Customer')),
                'member_since' => $user->created_at?->format('F Y'),
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        $user->detail()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'name' => $request->full_name,
                'contact' => $request->phone_number,
                'address' => $request->delivery_address,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'name' => $request->full_name,
                'phone' => $request->phone_number,
                'address' => $request->delivery_address,
            ]
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Verify current password against user_detail password
        $userDetail = $user->detail;
        if (!$userDetail || !Hash::check($request->current_password, $userDetail->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['Current password is incorrect']]
            ], 422);
        }

        $newPasswordHash = Hash::make($request->new_password);

        $userDetail->update([
            'password' => $newPasswordHash,
        ]);

        $this->storePasswordHashInSession($request, (int) $user->user_id, $newPasswordHash);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Verify current password
     */
    public function verifyCurrentPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $passwordHash = $this->passwordHashForFastVerification($request, (int) $user->user_id);

        if (!$passwordHash || !Hash::check($request->current_password, $passwordHash)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password verified'
        ]);
    }

    private function passwordHashForFastVerification(Request $request, int $userId): ?string
    {
        $sessionUserId = (int) $request->session()->get('auth.password_hash_user_id', 0);
        $sessionHash = $request->session()->get('auth.password_hash');

        if ($sessionUserId === $userId && is_string($sessionHash) && $sessionHash !== '') {
            return $sessionHash;
        }

        $user = Auth::user();
        $user->loadMissing('detail');
        $passwordHash = $user->detail?->password;

        if (is_string($passwordHash) && $passwordHash !== '') {
            $this->storePasswordHashInSession($request, $userId, $passwordHash);

            return $passwordHash;
        }

        return null;
    }

    private function storePasswordHashInSession(Request $request, int $userId, string $passwordHash): void
    {
        $request->session()->put([
            'auth.password_hash_user_id' => $userId,
            'auth.password_hash' => $passwordHash,
        ]);
    }

    /**
     * Generate avatar initials from name
     */
    private function generateAvatar(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';
        
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        
        return $initials ?: 'BC';
    }
}
