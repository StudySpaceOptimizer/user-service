<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Http\Requests\UserProfileRequest;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function getAllUsers(UserProfileRequest $request)
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
        if (isset($filters['is_in'])) {
            $query->where('is_in', $filters['is_in']);
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

    public function getMyUser(Request $request)
    {
        $userInfo = $request->header('X-User-Info');

        if (!$userInfo) {
            return response()->json(['error' => 'X-User-Info header is missing'], 400);
        }

        $decodedInfo = json_decode($userInfo, true);

        if (!isset($decodedInfo['email'])) {
            return response()->json(['error' => 'Email is missing in X-User-Info'], 400);
        }

        $email = $decodedInfo['email'];

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

    public function getUsersCount()
    {
        $normalCount = UserProfile::where('role', 'user')->count();
        $adminCount = UserProfile::where('role', 'admin')->count();

        return response()->json([
            'normal' => $normalCount,
            'admin' => $adminCount,
        ]);
    }
}
