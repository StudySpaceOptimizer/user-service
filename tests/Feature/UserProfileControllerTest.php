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
        UserProfile::factory()->count(15)->create();

        $response = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 0,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'email',
                        'is_in',
                        'point',
                        'name',
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
        UserProfile::factory()->create([
            'email' => 'testuser@example.com',
            'is_in' => true,
            'name' => 'Test User',
            'role' => 'user',
        ]);

        UserProfile::factory()->create([
            'email' => 'anotheruser@example.com',
            'is_in' => false,
            'name' => 'Another User',
            'role' => 'user',
        ]);

        $response = $this->json('GET', '/api/users', [
            'filters' => [
                'email' => 'testuser@example.com',
                'is_in' => true,
                'name' => 'Test',
                'role' => 'user',
            ],
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'testuser@example.com')
            ->assertJsonPath('data.0.is_in', 1);
    }

    public function testIndexFiltersUsersWithUserId()
    {
        UserProfile::factory()->create([
            'email' => 'testuser@example.com',
            'is_in' => true,
            'name' => 'Test User',
            'role' => 'user',
        ]);

        UserProfile::factory()->create([
            'email' => 'anotheruser@example.com',
            'is_in' => false,
            'name' => 'Another User',
            'role' => 'admin',
        ]);

        $response = $this->json('GET', '/api/users', [
            'filters' => [
                'email' => 'testuser@example.com',
            ],
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'testuser@example.com')
            ->assertJsonPath('data.0.name', 'Test User');
    }


    /**
     * 測試分頁功能
     */
    public function testIndexPagination()
    {
        UserProfile::factory()->count(20)->create();

        $responsePage1 = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 0,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $responsePage2 = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 10,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

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
        $response = $this->json('GET', '/api/users', [
            'pageSize' => 'invalid',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('pageSize');
    }

    public function testGetMyProfileSuccess()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_in' => true,
            'point' => 10,
            'role' => 'user',
        ]);

        $response = $this->get('/api/users/me', [
            'X-User-Email' => 'test@example.com',
            'X-User-Role' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'test@example.com',
                'name' => 'Test User',
                'is_in' => true,
                'point' => 10,
                'role' => 'user',
            ]);
    }

    public function testGetMyProfileNotFound()
    {
        $response = $this->get('/api/users/me', [
            'X-User-Email' => 'not-exist@example.com',
            'X-User-Role' => 'user',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    public function testUpdateMyProfileSuccess()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name',
        ]);

        $response = $this->put('/api/users/me', [
            'name' => 'New Name',
        ], [
            'X-User-Email' => 'test@example.com',
            'X-User-Role' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('user_profiles', [
            'email' => 'test@example.com',
            'name' => 'New Name',
        ]);
    }

    public function testUpdateMyProfileNotFound()
    {
        $response = $this->put('/api/users/me', [
            'name' => 'New Name',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    public function testUpdateMyProfileInvalidInput()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name',
        ]);

        $response = $this->put('/api/users/me', [
            'name' => '',
        ], [
            'X-User-Email' => 'test@example.com',
            'X-User-Role' => 'user',
        ]);


        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                ],
            ]);
    }

    public function testBanUser()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson("/api/users/{$user->email}/ban", [
            'reason' => 'Violation of terms',
            'end_at' => now()->addDays(7)->toISOString(),
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('user_profiles', [
            'email' => $user->email,
            'ban_reason' => 'Violation of terms',
        ]);
    }

    public function testBanUserNotFound()
    {
        $response = $this->postJson("/api/users/nonexistent@example.com/ban", [
            'reason' => 'Violation of terms',
            'end_at' => now()->addDays(7)->toISOString(),
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    public function testBanUserValidationFails()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson("/api/users/{$user->email}/ban", [
            'reason' => '',
            'end_at' => 'invalid-date',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason', 'end_at']);
    }

    public function testUnbanUser()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'ban_reason' => 'Violation of terms',
            'ban_end_at' => now()->addDays(7),
        ]);

        $response = $this->deleteJson("/api/users/{$user->email}/ban", [], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('user_profiles', [
            'email' => $user->email,
            'ban_reason' => null,
            'ban_end_at' => null,
        ]);
    }
    public function testUnbanUserNotFound()
    {
        $response = $this->deleteJson("/api/users/nonexistent@example.com/ban", [], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }
    public function testUpdateUserPoints()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'point' => 5,
        ]);

        $response = $this->putJson("/api/users/{$user->email}/points", [
            'points' => 8,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('user_profiles', [
            'email' => $user->email,
            'point' => 8,
        ]);
    }

    public function testUpdateUserPointsFails()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'point' => 5,
        ]);

        $response = $this->putJson("/api/users/{$user->email}/points", [
            'points' => 'invalid',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'points' => ['The points field must be an integer.'],
            ]);
    }

    public function testAutoBanUserWhenPointsExceed10()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'point' => 8,
        ]);

        $response = $this->putJson("/api/users/{$user->email}/points", [
            'points' => 13,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('user_profiles', [
            'email' => $user->email,
            'point' => 13,
            'ban_reason' => '違規計點超過 10 點自動封禁',
        ]);
    }

    public function testUpdateUserPointsUserNotFound()
    {
        $response = $this->putJson("/api/users/nonexistent@example.com/points", [
            'points' => 5,
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    public function testGrantRoleSuccess()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $response = $this->postJson("/api/users/{$user->email}/grant-role", [
            'role' => 'admin',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('user_profiles', [
            'email' => $user->email,
            'role' => 'admin',
        ]);
    }

    public function testGrantRoleInvalidRole()
    {
        $user = UserProfile::factory()->create([
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $response = $this->postJson("/api/users/{$user->email}/grant-role", [
            'role' => 'invalid-role',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('role');
    }

    public function testGrantRoleUserNotFound()
    {
        $response = $this->postJson("/api/users/nonexistent@example.com/grant-role", [
            'role' => 'admin',
        ], [
            'X-User-Email' => 'admin@example.com',
            'X-User-Role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }
}
