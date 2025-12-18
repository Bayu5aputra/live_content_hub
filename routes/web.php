<?php

use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Protected Routes (Requires Login via Alpine/JavaScript)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Manage Organizations
    Route::get('/organizations', function () {
        return view('super-admin.organizations.index');
    })->name('organizations.index');

    Route::get('/organizations/create', function () {
        return view('super-admin.organizations.create');
    })->name('organizations.create');

    Route::get('/organizations/{slug}/edit', function ($slug) {
        return view('super-admin.organizations.edit', ['slug' => $slug]);
    })->name('organizations.edit');

    // Manage Super Admins
    Route::get('/admins', function () {
        return view('super-admin.admins.index');
    })->name('admins.index');

    //Tambahab
    Route::get('/organizations/{slug}/view', function ($slug) {
        return view('super-admin.organizations.view', ['slug' => $slug]);
    })->name('organizations.view');
});

/*
|--------------------------------------------------------------------------
| Organization Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('organizations/{organization}')->name('organization.')->group(function () {
    // Contents Management
    Route::get('/contents', function ($organization) {
        return view('organization.contents.index', ['organization' => $organization]);
    })->name('contents.index');

    Route::get('/contents/create', function ($organization) {
        return view('organization.contents.create', ['organization' => $organization]);
    })->name('contents.create');

    // Playlists Management
    Route::get('/playlists', function ($organization) {
        return view('organization.playlists.index', ['organization' => $organization]);
    })->name('playlists.index');

    Route::get('/playlists/create', function ($organization) {
        return view('organization.playlists.create', ['organization' => $organization]);
    })->name('playlists.create');

    Route::get('/playlists/{playlist}', function ($organization, $playlist) {
        return view('organization.playlists.show', [
            'organization' => $organization,
            'playlist' => $playlist
        ]);
    })->name('playlists.show');

    // Users Management (Admin only)
    Route::get('/users', function ($organization) {
        return view('organization.users.index', ['organization' => $organization]);
    })->name('users.index');

    // View Contents (Read-only for regular users)
    Route::get('/view-contents', function ($organization) {
        return view('organization.contents.view', ['organization' => $organization]);
    })->name('contents.view');
});

/*
|--------------------------------------------------------------------------
| Public Display Routes
|--------------------------------------------------------------------------
*/
Route::get('/display/{organization}', function ($organization) {
    return view('display.show', ['organization' => $organization]);
})->name('display.show');

Route::get('/display/{organization}/{playlist}', function ($organization, $playlist) {
    return view('display.show', [
        'organization' => $organization,
        'playlist' => $playlist
    ]);
})->name('display.playlist');
