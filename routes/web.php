<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Login via Alpine/JavaScript)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/organizations', function () {
        return view('admin.organizations.index');
    })->name('organizations.index');

    Route::get('/organizations/create', function () {
        return view('admin.organizations.create');
    })->name('organizations.create');
});

/*
|--------------------------------------------------------------------------
| Organization Routes
|--------------------------------------------------------------------------
*/
Route::prefix('{organization}')->group(function () {
    // Contents
    Route::get('/contents', function ($organization) {
        return view('organization.contents.index', ['organization' => $organization]);
    })->name('organization.contents.index');

    Route::get('/contents/create', function ($organization) {
        return view('organization.contents.create', ['organization' => $organization]);
    })->name('organization.contents.create');

    // Playlists
    Route::get('/playlists', function ($organization) {
        return view('organization.playlists.index', ['organization' => $organization]);
    })->name('organization.playlists.index');

    Route::get('/playlists/create', function ($organization) {
        return view('organization.playlists.create', ['organization' => $organization]);
    })->name('organization.playlists.create');

    Route::get('/playlists/{playlist}', function ($organization, $playlist) {
        return view('organization.playlists.show', [
            'organization' => $organization,
            'playlist' => $playlist
        ]);
    })->name('organization.playlists.show');
});

/*
|--------------------------------------------------------------------------
| Public Display Routes
|--------------------------------------------------------------------------
*/
Route::get('/{organization}/display', function ($organization) {
    return view('display.show', ['organization' => $organization]);
})->name('display.show');

Route::get('/{organization}/display/{playlist}', function ($organization, $playlist) {
    return view('display.show', [
        'organization' => $organization,
        'playlist' => $playlist
    ]);
})->name('display.playlist');
