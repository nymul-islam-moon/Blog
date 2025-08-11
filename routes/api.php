<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAdminController;
use App\Http\Controllers\Api\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth (Public)
Route::post('/register', [AuthController::class, 'register']);  // Request: Auth — Register
Route::post('/login',    [AuthController::class, 'login']);     // Request: Auth — Login

// Authenticated (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);  // Request: Auth — Logout
    Route::get('/me',      [AuthController::class, 'me']);      // Request: Auth — Me

    // Articles (policy-enforced)
    Route::get('/articles',                   [ArticleController::class, 'index']);     // Request: Articles — List
    Route::get('/articles/{article}',         [ArticleController::class, 'show']);      // Request: Articles — Show
    Route::post('/articles',                  [ArticleController::class, 'store']);     // Request: Articles — Create
    Route::put('/articles/{article}',         [ArticleController::class, 'update']);    // Request: Articles — Update
    Route::patch('/articles/{article}',       [ArticleController::class, 'update']);    // Request: Articles — Update
    Route::post('/articles/{article}/publish',[ArticleController::class, 'publish']);   // Request: Articles — Publish
    Route::delete('/articles/{article}',      [ArticleController::class, 'destroy']);   // Request: Articles — Delete
});

// Admin-only
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', [UserAdminController::class, 'index']);                       // Request: Users — All Users
    Route::post('/users/{user}/assign-role', [UserAdminController::class, 'assignRole']); // Request: Users — Assign Role
});
