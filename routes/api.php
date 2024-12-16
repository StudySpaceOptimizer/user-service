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

Route::get('/users', [UserProfileController::class, 'getAllProfile'])->middleware('parse.user.info');
Route::get('/users/me', [UserProfileController::class, 'getMyProfile'])->middleware('parse.user.info');
Route::put('/users/me', [UserProfileController::class, 'updateMyProfile'])->middleware('parse.user.info');
Route::post('/users/{email}/ban', [UserProfileController::class, 'banUser'])->middleware('parse.user.info');
Route::delete('/users/{email}/ban', [UserProfileController::class, 'unbanUser'])->middleware('parse.user.info');
Route::put('/users/{email}/points', [UserProfileController::class, 'updateUserPoints'])->middleware('parse.user.info');
Route::post('/users/{email}/grant-role', [UserProfileController::class, 'grantRole'])->middleware('parse.user.info');
Route::get('/auth/callback', [AuthController::class, 'callback']);
Route::get('/settings', [SettingsController::class, 'getSettings']);
Route::put('/settings', [SettingsController::class, 'updateSettings']);
