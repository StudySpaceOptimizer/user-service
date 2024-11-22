<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // 使用 Guzzle HTTP
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function callback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return response()->json(['error' => 'Authorization code is missing'], 400);
        }

        $tokenResponse = Http::asForm()->post(config('app.oauth_token_url'), [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => config('app.oauth_client_id'),
            'client_secret' => config('app.oauth_client_secret'),
            'redirect_uri' => config('app.oauth_redirect_uri'),
        ]);

        if (!$tokenResponse->ok()) {
            return response()->json([
                'error' => 'Failed to exchange token',
                'response' => $tokenResponse->json(),
                'data' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => config('app.oauth_client_id'),
                    'client_secret' => config('app.oauth_client_secret'),
                    'redirect_uri' => config('app.oauth_redirect_uri'),
                ]
            ], $tokenResponse->status());   
        }

        $accessToken = $tokenResponse->json()['access_token'];

        $profileResponse = Http::withToken($accessToken)->get(config('app.oauth_profile_url'));
        if (!$profileResponse->ok()) {
            return response()->json(['error' => 'Failed to fetch user profile'], $profileResponse->status());
        }

        $profile = $profileResponse->json();
        $email = $profile['email'] ?? null;
        if (!$email) {
            return response()->json(['error' => 'Email not found in user profile'], 400);
        }

        $user = UserProfile::firstOrCreate(['email' => $email], [
            'name' => $profile['name'] ?? 'Guest',
            'role' => 'user',
        ]);

        if (!$user->getKey()) {
            return response()->json(['error' => 'Failed to set primary key'], 500);
        }

        $jwt = JWTAuth::fromUser($user);

        return response()->json(['message' => 'Login successful'])->cookie(
            'jwt_token',
            $jwt,
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }
}
