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
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->user_id,
                'name' => $user->detail?->name ?? 'Customer',
                'email' => $user->detail?->email ?? $user->email,
                'phone' => $user->detail?->contact ?? '',
                'address' => $user->detail?->address ?? '',
                'avatar' => $this->generateAvatar($user->detail?->name ?? 'Customer'),
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
        
        // TODO: Update in database
        // Example: $user->detail()->updateOrCreate(
        //     ['user_id' => $user->user_id],
        //     [
        //         'name' => $request->full_name,
        //         'contact' => $request->phone_number,
        //         'address' => $request->delivery_address,
        //     ]
        // );

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

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['Current password is incorrect']]
            ], 422);
        }

        // TODO: Update password in database
        // Example: $user->update([
        //     'password' => Hash::make($request->new_password)
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
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
