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
        ]);

        $responsePage2 = $this->json('GET', '/api/users', [
            'pageSize' => 10,
            'pageOffset' => 10,
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
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('pageSize');
    }

    public function testGetProfileSuccess()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_in' => true,
            'point' => 10,
            'role' => 'user',
        ]);

        $response = $this->withHeaders([
            'X-User-Info' => json_encode(['email' => 'test@example.com']),
        ])->get('/api/users/me');

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'test@example.com',
                'name' => 'Test User',
                'is_in' => true,
                'point' => 10,
                'role' => 'user',
            ]);
    }

    public function testGetProfileNotFound()
    {
        $response = $this->withHeaders([
            'X-User-Info' => json_encode(['email' => 'nonexistent@example.com']),
        ])->get('/api/users/me');

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    public function testHeaderMissing()
    {
        $response = $this->get('/api/users/me');

        $response->assertStatus(400)
            ->assertJson(['error' => 'X-User-Info header is missing']);
    }

    public function testUpdateMyProfileSuccess()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name',
        ]);

        $response = $this->withHeaders([
            'X-User-Info' => json_encode(['email' => 'test@example.com']),
        ])->put('/api/users/me', [
                    'name' => 'New Name',
                ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('user_profiles', [
            'email' => 'test@example.com',
            'name' => 'New Name',
        ]);
    }

    public function testUpdateMyProfileMissingHeader()
    {
        $response = $this->put('/api/users/me', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'X-User-Info header is missing']);
    }

    public function testUpdateMyProfileInvalidInput()
    {
        UserProfile::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name',
        ]);

        $response = $this->withHeaders([
            'X-User-Info' => json_encode(['email' => 'test@example.com']),
        ])->put('/api/users/me', [
                    'name' => '',
                ]);


        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                ],
            ]);
    }

    public function testGetUsersCount()
    {
        UserProfile::factory()->count(5)->create(['role' => 'user']);
        UserProfile::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->get('/api/users/count');

        $response->assertStatus(200)
            ->assertJson([
                'normal' => 5,
                'admin' => 2,
            ]);
    }
}
