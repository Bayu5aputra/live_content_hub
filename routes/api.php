<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationUserController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\DisplayController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// Public Display Routes
Route::prefix('display')
    ->name('api.display.')
    ->group(function () {
        Route::get('/{organization:slug}', [DisplayController::class, 'show'])->name('show');
        Route::get('/{organization:slug}/playlist/{playlist}', [DisplayController::class, 'showPlaylist'])->name('playlist');
    });

// Protected Routes (Sanctum Required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Functions
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Super Admin Routes
    Route::prefix('super-admin')
        ->middleware('super.admin')
        ->name('api.super-admin.')
        ->group(function () {
            // Manage Super Admins
            Route::get('admins', [SuperAdminController::class, 'index'])->name('admins.index');
            Route::post('admins', [SuperAdminController::class, 'store'])->name('admins.store');
            Route::patch('admins/{user}/password', [SuperAdminController::class, 'updatePassword'])->name('admins.password');
            Route::delete('admins/{user}', [SuperAdminController::class, 'destroy'])->name('admins.destroy');

            // Manage Organizations
            Route::apiResource('organizations', OrganizationController::class)->names('organizations');
            Route::patch('organizations/{organization:slug}/toggle-status', [OrganizationController::class, 'toggleStatus'])->name('organizations.toggle-status');
        });

    // Organization Admin Routes - Manage Users
    Route::prefix('organizations/{organization:slug}')
        ->middleware('org.owner')
        ->name('api.organizations.')
        ->group(function () {
            Route::get('users', [OrganizationUserController::class, 'index'])->name('users.index');
            Route::post('users', [OrganizationUserController::class, 'store'])->name('users.store');
            Route::patch('users/{user}/role', [OrganizationUserController::class, 'updateRole'])->name('users.role');
            Route::patch('users/{user}/password', [OrganizationUserController::class, 'updatePassword'])->name('users.password');
            Route::delete('users/{user}', [OrganizationUserController::class, 'destroy'])->name('users.destroy');
        });

    // Organization Editor Routes - Content Management
    Route::middleware('org.access:editor')
        ->prefix('organizations/{organization:slug}')
        ->name('api.organizations.')
        ->group(function () {
            Route::apiResource('contents', ContentController::class)->names('contents');
            Route::post('contents/reorder', [ContentController::class, 'reorder'])->name('contents.reorder');
            Route::patch('contents/{content}/toggle-status', [ContentController::class, 'toggleStatus'])->name('contents.toggle-status');

            Route::apiResource('playlists', PlaylistController::class)->names('playlists');
            Route::post('playlists/{playlist}/contents', [PlaylistController::class, 'addContent'])->name('playlists.contents.add');
            Route::delete('playlists/{playlist}/contents/{content}', [PlaylistController::class, 'removeContent'])->name('playlists.contents.remove');
            Route::post('playlists/{playlist}/reorder', [PlaylistController::class, 'reorderContents'])->name('playlists.reorder');
            Route::patch('playlists/{playlist}/toggle-status', [PlaylistController::class, 'toggleStatus'])->name('playlists.toggle-status');
        });

    // Organization User Routes (Read-Only)
    Route::middleware('org.access:user')
        ->prefix('organizations/{organization:slug}')
        ->name('api.organizations.')
        ->group(function () {
            Route::get('contents-view', [ContentController::class, 'index'])->name('contents.view');
            Route::get('contents-view/{content}', [ContentController::class, 'show'])->name('contents.show-view');
        });
});
