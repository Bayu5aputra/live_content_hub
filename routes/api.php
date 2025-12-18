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

            // BARU: Manage Organization Admins
            Route::get('organizations/{organization:slug}/admins', [OrganizationController::class, 'getAdmins'])->name('organizations.admins.index');
            Route::post('organizations/{organization:slug}/admins', [OrganizationController::class, 'addAdmin'])->name('organizations.admins.store');
            Route::delete('organizations/{organization:slug}/admins/{user}', [OrganizationController::class, 'removeAdmin'])->name('organizations.admins.destroy');
            Route::post('organizations/{organization:slug}/admins/{user}/reset-password', [OrganizationController::class, 'resetAdminPassword'])->name('organizations.admins.reset-password');
            // Super Admin Routes
            Route::prefix('super-admin')
                ->middleware('super.admin')
                ->name('api.super-admin.')
                ->group(function () {
                    // TAMBAHKAN INI
                    Route::get('stats', [SuperAdminController::class, 'stats'])->name('stats');

                    // Manage Super Admins
                    Route::get('admins', [SuperAdminController::class, 'index'])->name('admins.index');
                    // ... rest of routes
                });
        });

    // Organization Admin Routes - Manage Users
    Route::prefix('organizations/{organization:slug}')
        ->middleware('org.owner')
        ->name('api.organizations.')
        ->group(function () {
            // Manage Organization Admins (by Org Admin)
            Route::get('admins', [OrganizationUserController::class, 'indexAdmins'])->name('admins.index');
            Route::post('admins', [OrganizationUserController::class, 'storeAdmin'])->name('admins.store');
            Route::delete('admins/{user}', [OrganizationUserController::class, 'destroyAdmin'])->name('admins.destroy');
            Route::post('admins/{user}/reset-password', [OrganizationUserController::class, 'resetAdminPassword'])->name('admins.reset-password');

            // Manage Users (Read-only users)
            Route::get('users', [OrganizationUserController::class, 'indexUsers'])->name('users.index');
            Route::post('users', [OrganizationUserController::class, 'storeUser'])->name('users.store');
            Route::delete('users/{user}', [OrganizationUserController::class, 'destroyUser'])->name('users.destroy');
            Route::post('users/{user}/reset-password', [OrganizationUserController::class, 'resetUserPassword'])->name('users.reset-password');
        });

    // Organization Admin Routes - Content Management
    Route::middleware('org.access:admin')
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
            Route::get('contents-readonly', [ContentController::class, 'index'])->name('contents.readonly');
            Route::get('contents-readonly/{content}', [ContentController::class, 'show'])->name('contents.show-readonly');
            Route::get('playlists-readonly', [PlaylistController::class, 'index'])->name('playlists.readonly');
            Route::get('playlists-readonly/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show-readonly');
        });
});
