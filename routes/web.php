<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\DisplayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Super Admin Routes - Organization Management
// Hanya admin yang bisa akses
Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('organizations', OrganizationController::class);
    Route::post('organizations/{organization}/users', [OrganizationController::class, 'addUser'])
        ->name('organizations.users.add');
    Route::delete('organizations/{organization}/users/{user}', [OrganizationController::class, 'removeUser'])
        ->name('organizations.users.remove');
});

// Organization Routes - Content Management
// Memerlukan akses ke organization minimal sebagai viewer
Route::middleware(['auth', 'org.access:viewer'])->prefix('{organization}')->name('organization.')->group(function () {

    // Content Management - Minimal editor
    Route::middleware('org.access:editor')->group(function () {
        Route::resource('contents', ContentController::class);
        Route::post('contents/reorder', [ContentController::class, 'reorder'])
            ->name('contents.reorder');
    });

    // Playlist Management - Minimal editor
    Route::middleware('org.access:editor')->group(function () {
        Route::resource('playlists', PlaylistController::class);
        Route::post('playlists/{playlist}/contents', [PlaylistController::class, 'addContent'])
            ->name('playlists.contents.add');
        Route::delete('playlists/{playlist}/contents/{content}', [PlaylistController::class, 'removeContent'])
            ->name('playlists.contents.remove');
    });
});

// Public Display Routes - Tidak perlu auth
Route::get('/{organization}/display', [DisplayController::class, 'show'])
    ->name('display.show');
Route::get('/{organization}/display/{playlist}', [DisplayController::class, 'show'])
    ->name('display.playlist');

// API Routes for Display - Tidak perlu auth
Route::get('/{organization}/api/contents', [DisplayController::class, 'api'])
    ->name('display.api');
Route::get('/{organization}/api/playlist/{playlist}', [DisplayController::class, 'api'])
    ->name('display.playlist.api');
