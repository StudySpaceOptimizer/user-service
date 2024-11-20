<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Http\Requests\UserProfileRequest;

class UserProfileController extends Controller
{
    public function index(UserProfileRequest $request)
    {
        $validated = $request->validated(); // 驗證後的資料
        $pageSize = $validated['pageSize'] ?? 10;
        $pageOffset = $validated['pageOffset'] ?? 0;
        $filters = $validated['filters'] ?? [];

        // 基本查詢
        $query = UserProfile::query();

        // 過濾條件
        if (!empty($filters['user_id'])) {
            $query->where('id', $filters['user_id']);
        }
        if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }
        if (isset($filters['is_in'])) {
            $query->where('is_in', $filters['is_in']);
        }
        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        // 分頁
        $userProfiles = $query->skip($pageOffset)->take($pageSize)->get();
        $count = $userProfiles->count();

        return response()->json([
            'data' => $userProfiles,
            'count' => $count,
        ]);
    }
}
