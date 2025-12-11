<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\DisplayController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public Display Routes - No auth required
Route::prefix('display')->group(function () {
    Route::get('/{organization:slug}', [DisplayController::class, 'show']);
    Route::get('/{organization:slug}/playlist/{playlist}', [DisplayController::class, 'showPlaylist']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Super Admin - Organization Management
    Route::prefix('admin')->group(function () {
        Route::apiResource('organizations', OrganizationController::class);
        Route::post('organizations/{organization}/users', [OrganizationController::class, 'addUser']);
        Route::delete('organizations/{organization}/users/{user}', [OrganizationController::class, 'removeUser']);
        Route::patch('organizations/{organization}/toggle-status', [OrganizationController::class, 'toggleStatus']);
    });

    // Organization - Content & Playlist Management
    Route::prefix('organizations/{organization:slug}')->group(function () {
        // Contents
        Route::apiResource('contents', ContentController::class);
        Route::post('contents/reorder', [ContentController::class, 'reorder']);
        Route::patch('contents/{content}/toggle-status', [ContentController::class, 'toggleStatus']);

        // Playlists
        Route::apiResource('playlists', PlaylistController::class);
        Route::post('playlists/{playlist}/contents', [PlaylistController::class, 'addContent']);
        Route::delete('playlists/{playlist}/contents/{content}', [PlaylistController::class, 'removeContent']);
        Route::post('playlists/{playlist}/reorder', [PlaylistController::class, 'reorderContents']);
        Route::patch('playlists/{playlist}/toggle-status', [PlaylistController::class, 'toggleStatus']);
    });
});
