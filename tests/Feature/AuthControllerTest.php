<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\UserProfile;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCallbackWithoutCode()
    {
        $response = $this->get('/api/auth/callback');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Authorization code is missing']);
    }

    public function testCallbackTokenExchangeFails()
    {
        Http::fake([
            config('app.oauth_token_url') => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $response = $this->get('/api/auth/callback?code=invalid_code');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Failed to exchange token']);
    }

    public function testCallbackProfileFetchFails()
    {
        Http::fake([
            config('app.oauth_token_url') => Http::response(['access_token' => 'valid_token'], 200),
            config('app.oauth_profile_url') => Http::response(['error' => 'invalid_token'], 401),
        ]);

        $response = $this->get('/api/auth/callback?code=valid_code');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Failed to fetch user profile']);
    }

    public function testCallbackEmailNotFoundInProfile()
    {
        Http::fake([
            config('app.oauth_token_url') => Http::response(['access_token' => 'valid_token'], 200),
            config('app.oauth_profile_url') => Http::response(['name' => 'Test User'], 200),
        ]);

        $response = $this->get('/api/auth/callback?code=valid_code');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Email not found in user profile']);
    }

    public function testCallbackCreatesNewUserAndReturnsJwt()
    {
        Http::fake([
            config('app.oauth_token_url') => Http::response(['access_token' => 'valid_token'], 200),
            config('app.oauth_profile_url') => Http::response([
                'email' => 'test@example.com',
            ], 200),
        ]);

        $response = $this->get('/api/auth/callback?code=valid_code');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Login successful']);

        $this->assertDatabaseHas('user_profiles', [
            'email' => 'test@example.com',
            'name' => 'test',
            'role' => 'user',
        ]);

        $response->assertCookie('jwt_token');
    }

    public function testCallbackReturnsJwtForExistingUser()
    {
        UserProfile::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        Http::fake([
            config('app.oauth_token_url') => Http::response(['access_token' => 'valid_token'], 200),
            config('app.oauth_profile_url') => Http::response(['email' => 'existing@example.com'], 200),
        ]);

        $response = $this->get('/api/auth/callback?code=valid_code');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Login successful']);

        $this->assertDatabaseHas('user_profiles', [
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        $response->assertCookie('jwt_token');
    }
}
