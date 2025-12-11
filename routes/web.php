<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\DisplayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FIX: Tambahkan route login dummy agar Sanctum tidak error
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return response()->json([
        'message' => 'Silakan login melalui API (POST /api/login)'
    ], 401);
})->name('login');

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Harus Login + Super Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('organizations', OrganizationController::class);

        Route::post('organizations/{organization}/users', [OrganizationController::class, 'addUser'])
            ->name('organizations.users.add');

        Route::delete('organizations/{organization}/users/{user}', [OrganizationController::class, 'removeUser'])
            ->name('organizations.users.remove');
    });

/*
|--------------------------------------------------------------------------
| Organization Routes (Harus Login + Akses ke Organization)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('{organization}')
    ->name('organization.')
    ->group(function () {

        // Minimal Editor -> Content
        Route::middleware('org.access:editor')->group(function () {
            Route::resource('contents', ContentController::class);
            Route::post('contents/reorder', [ContentController::class, 'reorder'])
                ->name('contents.reorder');
        });

        // Minimal Editor -> Playlist
        Route::middleware('org.access:editor')->group(function () {
            Route::resource('playlists', PlaylistController::class);
            Route::post('playlists/{playlist}/contents', [PlaylistController::class, 'addContent'])
                ->name('playlists.contents.add');
            Route::delete('playlists/{playlist}/contents/{content}', [PlaylistController::class, 'removeContent'])
                ->name('playlists.contents.remove');
        });
    });

/*
|--------------------------------------------------------------------------
| Public Display Routes (Tidak Perlu Auth)
|--------------------------------------------------------------------------
*/
Route::get('/{organization}/display', [DisplayController::class, 'show'])
    ->name('display.show');

Route::get('/{organization}/display/{playlist}', [DisplayController::class, 'show'])
    ->name('display.playlist');

/*
|--------------------------------------------------------------------------
| Public API for Display (Tidak Perlu Auth)
|--------------------------------------------------------------------------
*/
Route::get('/{organization}/api/contents', [DisplayController::class, 'api'])
    ->name('display.api');

Route::get('/{organization}/api/playlist/{playlist}', [DisplayController::class, 'api'])
    ->name('display.playlist.api');
