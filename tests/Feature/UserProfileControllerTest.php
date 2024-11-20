<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\UserProfile;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 測試獲取所有用戶資料（不帶篩選條件）
     */
    public function testIndexReturnsAllUsers()
    {
        // 建立假資料
        UserProfile::factory()->count(15)->create();

        // 發送請求
        $response = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 0,
        ]);

        // 驗證響應
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data') // 確認返回 10 筆資料
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'email',
                        'is_in',
                        'point',
                        'name',
                        'phone',
                        'id_card',
                    ],
                ],
                'count',
            ]);
    }

    /**
     * 測試帶過濾條件的請求
     */
    public function testIndexFiltersUsers()
    {
        // 建立假資料
        UserProfile::factory()->create([
            'email' => 'testuser@example.com',
            'is_in' => true,
            'name' => 'Test User',
            'id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        UserProfile::factory()->create([
            'email' => 'anotheruser@example.com',
            'is_in' => false,
            'name' => 'Another User',
            'id' => '223e4567-e89b-12d3-a456-426614174000',
        ]);

        // 發送請求帶過濾條件
        $response = $this->json('GET', '/api/users', [
            'filters' => [
                'email' => 'testuser@example.com',
                'is_in' => true,
                'name' => 'Test',
                'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            ],
        ]);

        // 驗證響應
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // 應只返回 1 筆
            ->assertJsonPath('data.0.email', 'testuser@example.com') // 確認返回的用戶
            ->assertJsonPath('data.0.is_in', 1);
    }

    public function testIndexFiltersUsersWithUserId()
    {
        // 建立假資料
        UserProfile::factory()->create([
            'email' => 'testuser@example.com',
            'is_in' => true,
            'name' => 'Test User',
            'id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        UserProfile::factory()->create([
            'email' => 'anotheruser@example.com',
            'is_in' => false,
            'name' => 'Another User',
            'id' => '223e4567-e89b-12d3-a456-426614174000',
        ]);

        // 發送請求帶過濾條件
        $response = $this->json('GET', '/api/users', [
            'filters' => [
                'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            ],
        ]);

        // 驗證響應
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // 應只返回 1 筆資料
            ->assertJsonPath('data.0.id', '123e4567-e89b-12d3-a456-426614174000') // 驗證返回的 ID
            ->assertJsonPath('data.0.email', 'testuser@example.com') // 驗證返回的 email
            ->assertJsonPath('data.0.name', 'Test User'); // 驗證返回的 name
    }


    /**
     * 測試分頁功能
     */
    public function testIndexPagination()
    {
        // 建立假資料
        UserProfile::factory()->count(20)->create();

        // 發送第一頁請求
        $responsePage1 = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 0,
        ]);

        // 發送第二頁請求
        $responsePage2 = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 10,
        ]);

        // 驗證第一頁和第二頁返回資料不同
        $responsePage1->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $responsePage2->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $this->assertNotEquals(
            $responsePage1->json('data'),
            $responsePage2->json('data')
        );
    }

    /**
     * 測試驗證失敗的情況
     */
    public function testIndexValidationFails()
    {
        // 發送請求，缺少必要參數
        $response = $this->json('GET', '/api/users', [
            'pageSize' => 'invalid', // 無效的 pageSize
        ]);

        // 確認返回 422 狀態碼
        $response->assertStatus(422)
            ->assertJsonValidationErrors('pageSize');
    }
}
