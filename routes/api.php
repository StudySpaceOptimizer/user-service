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

Route::get('/users', [UserProfileController::class, 'getAllProfile']);
Route::get('/users/me', [UserProfileController::class, 'getMyProfile']);
Route::put('/users/me', [UserProfileController::class, 'updateMyProfile']);
Route::get('/users/count', [UserProfileController::class, 'getUsersCount']);
Route::get('/auth/callback', [AuthController::class, 'callback']);
