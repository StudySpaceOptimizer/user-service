<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Http\Requests\UserProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    public function getAllProfile(UserProfileRequest $request)
    {
        $validated = $request->validated(); // 驗證後的資料
        $pageSize = $validated['pageSize'] ?? 10;
        $pageOffset = $validated['pageOffset'] ?? 0;
        $filters = $validated['filters'] ?? [];

        // 基本查詢
        $query = UserProfile::query();

        // 過濾條件
        if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }
        if (isset($filters['isIn'])) {
            $query->where('is_in', $filters['isIn']);
        }
        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // 分頁
        $userProfiles = $query->skip($pageOffset)->take($pageSize)->get();
        $count = $userProfiles->count();

        return response()->json([
            'data' => $userProfiles,
            'count' => $count,
        ]);
    }

    public function getMyProfile(Request $request)
    {
        $email = $request->input('user.email');

        $userProfile = UserProfile::where('email', $email)->first();

        if (!$userProfile) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'email' => $userProfile->email,
            'name' => $userProfile->name,
            'is_in' => $userProfile->is_in,
            'point' => $userProfile->point,
            'role' => $userProfile->role,
        ]);
    }

    public function updateMyProfile(Request $request)
    {
        $email = $request->input('user.email');

        $user = UserProfile::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user->name = $request->input('name');
        $user->save();

        return response()->json(['message' => 'Profile updated successfully'], 200);
    }

    public function banUser(Request $request, $email)
    {
        $user = UserProfile::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:255',
            'end_at' => 'required|date|after:now',
        ]);

        $user->update([
            'ban_reason' => $data['reason'],
            'ban_end_at' => $data['end_at'],
        ]);

        return response()->noContent();
    }

    public function unbanUser($email)
    {
        $user = UserProfile::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update([
            'ban_reason' => null,
            'ban_end_at' => null,
        ]);

        return response()->noContent();
    }

    public function updateUserPoints(Request $request, $email)
    {
        $user = UserProfile::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            $data = $request->validate([
                'points' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        }

        $user->update([
            'point' => $data['points'],
        ]);

        if ($user->point > 10) {
            $user->update([
                'ban_reason' => '違規計點超過 10 點自動封禁',
                'ban_end_at' => Carbon::now()->addDays(7),
            ]);
        }

        return response()->noContent();
    }

    public function grantRole(Request $request, $email)
    {
        $user = UserProfile::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $data = $request->validate([
            'role' => 'required|string|in:user,admin',
        ]);

        $user->update(['role' => $data['role']]);

        return response()->noContent();
    }
}
