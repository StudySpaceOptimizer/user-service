<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;

Route::get('/users', [UserProfileController::class, 'getAllProfile']);
Route::get('/users/me', [UserProfileController::class, 'getMyProfile']);
Route::put('/users/me', [UserProfileController::class, 'updateMyProfile']);
Route::get('/users/count', [UserProfileController::class, 'getUsersCount']);
Route::post('/users/{email}/ban', [UserProfileController::class, 'banUser']);
Route::delete('/users/{email}/ban', [UserProfileController::class, 'unbanUser']);
Route::put('/users/{email}/points', [UserProfileController::class, 'updateUserPoints']);
Route::post('/users/{email}/grant-role', [UserProfileController::class, 'grantRole']);
Route::get('/auth/callback', [AuthController::class, 'callback']);
Route::get('/settings', [SettingsController::class, 'getSettings']);
Route::put('/settings', [SettingsController::class, 'updateSettings']);
