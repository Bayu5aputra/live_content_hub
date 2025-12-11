<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\DisplayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/login',    [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

/*
|--------------------------------------------------------------------------
| Public Display Routes
|--------------------------------------------------------------------------
*/
Route::prefix('display')
    ->name('api.display.')
    ->group(function () {

        Route::get('/{organization:slug}', [DisplayController::class, 'show'])
            ->name('show');

        Route::get('/{organization:slug}/playlist/{playlist}', [DisplayController::class, 'showPlaylist'])
            ->name('playlist');
    });

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth Functions
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user',    [AuthController::class, 'user'])->name('api.user');

    /*
    |--------------------------------------------------------------------------
    | Super Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('super.admin')
        ->prefix('admin')
        ->name('api.admin.')
        ->group(function () {

            // Organizations CRUD
            Route::apiResource('organizations', OrganizationController::class)
                ->names('organizations');

            // Manage users in organization
            Route::post('organizations/{organization}/users', [OrganizationController::class, 'addUser'])
                ->name('organizations.users.add');

            Route::delete('organizations/{organization}/users/{user}', [OrganizationController::class, 'removeUser'])
                ->name('organizations.users.remove');

            Route::patch('organizations/{organization}/toggle-status', [OrganizationController::class, 'toggleStatus'])
                ->name('organizations.toggle-status');
        });

    /*
    |--------------------------------------------------------------------------
    | Organization Editor Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('org.access:editor')
        ->prefix('organizations/{organization:slug}')
        ->name('api.organizations.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Contents
            |--------------------------------------------------------------------------
            */
            Route::apiResource('contents', ContentController::class)
                ->names('contents');

            Route::post('contents/reorder', [ContentController::class, 'reorder'])
                ->name('contents.reorder');

            Route::patch('contents/{content}/toggle-status', [ContentController::class, 'toggleStatus'])
                ->name('contents.toggle-status');

            /*
            |--------------------------------------------------------------------------
            | Playlists
            |--------------------------------------------------------------------------
            */
            Route::apiResource('playlists', PlaylistController::class)
                ->names('playlists');

            Route::post('playlists/{playlist}/contents', [PlaylistController::class, 'addContent'])
                ->name('playlists.contents.add');

            Route::delete('playlists/{playlist}/contents/{content}', [PlaylistController::class, 'removeContent'])
                ->name('playlists.contents.remove');

            Route::post('playlists/{playlist}/reorder', [PlaylistController::class, 'reorderContents'])
                ->name('playlists.reorder');

            Route::patch('playlists/{playlist}/toggle-status', [PlaylistController::class, 'toggleStatus'])
                ->name('playlists.toggle-status');
        });
});
