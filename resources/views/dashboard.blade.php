@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    user: null,
    organizations: [],
    loading: true,
    token: localStorage.getItem('token'),
    init() {
        if (!this.token) {
            window.location.href = '/login';
            return;
        }
        this.loadUser();
    },
    loadUser() {
        fetch('/api/user', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => {
            if (!r.ok) throw new Error('Unauthorized');
            return r.json();
        })
        .then(d => {
            this.user = d;
            this.organizations = d.organizations || [];
            this.loading = false;
        })
        .catch(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    }
}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1" x-show="user" x-text="'Welcome, ' + user?.name"></p>
    </div>

    <template x-if="loading">
        <div class="text-center py-12">
            <p class="text-gray-500">Loading...</p>
        </div>
    </template>

    <!-- Super Admin Panel -->
    <template x-if="!loading && user?.is_super_admin">
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white mb-6">
                <h2 class="text-2xl font-bold mb-2">Super Admin Panel</h2>
                <p class="text-blue-100">You have full access to manage the system</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Manage Organizations -->
                <a href="/super-admin/organizations"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 border-l-4 border-blue-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">Organizations</h3>
                    </div>
                    <p class="text-gray-600 mb-3">Create, edit, and manage all organizations</p>
                    <span class="text-blue-600 font-medium">Manage →</span>
                </a>

                <!-- Manage Super Admins -->
                <a href="/super-admin/admins"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 border-l-4 border-green-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">Super Admins</h3>
                    </div>
                    <p class="text-gray-600 mb-3">Add or remove super administrator accounts</p>
                    <span class="text-green-600 font-medium">Manage →</span>
                </a>

                <!-- System Stats -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">System</h3>
                    </div>
                    <p class="text-gray-600 mb-2">Total Organizations</p>
                    <p class="text-3xl font-bold text-purple-600" x-text="organizations.length"></p>
                </div>
            </div>
        </div>
    </template>

    <!-- Organization Admin Panel -->
    <template x-if="!loading && !user?.is_super_admin && organizations.length > 0">
        <div>
            <h2 class="text-2xl font-bold mb-4">Your Organizations</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="org in organizations" :key="org.id">
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold mb-1" x-text="org.name"></h3>
                            <p class="text-sm text-gray-500" x-text="org.slug"></p>
                            <span class="inline-block mt-2 px-2 py-1 text-xs rounded"
                                :class="org.role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                x-text="org.role.charAt(0).toUpperCase() + org.role.slice(1)"></span>
                        </div>

                        <!-- Admin Actions -->
                        <template x-if="org.role === 'admin'">
                            <div class="space-y-2">
                                <a :href="`/organizations/${org.slug}/contents`"
                                    class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center text-sm">
                                    Manage Contents
                                </a>
                                <a :href="`/organizations/${org.slug}/playlists`"
                                    class="block px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-center text-sm">
                                    Manage Playlists
                                </a>
                                <a :href="`/organizations/${org.slug}/users`"
                                    class="block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-center text-sm">
                                    Manage Users
                                </a>
                                <a :href="`/display/${org.slug}`" target="_blank"
                                    class="block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-center text-sm">
                                    View Display
                                </a>
                            </div>
                        </template>

                        <!-- User (Read-only) Actions -->
                        <template x-if="org.role === 'user'">
                            <div class="space-y-2">
                                <a :href="`/organizations/${org.slug}/view-contents`"
                                    class="block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-center text-sm">
                                    View Contents
                                </a>
                                <a :href="`/display/${org.slug}`" target="_blank"
                                    class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center text-sm">
                                    View Display
                                </a>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- No Organizations -->
    <template x-if="!loading && !user?.is_super_admin && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-500 mb-4">You don't have access to any organizations yet</p>
            <p class="text-sm text-gray-400">Contact your administrator to get access</p>
        </div>
    </template>
</div>
@endsection@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    user: null,
    organizations: [],
    loading: true,
    token: localStorage.getItem('token'),
    init() {
        if (!this.token) {
            window.location.href = '/login';
            return;
        }
        this.loadUser();
    },
    loadUser() {
        fetch('/api/user', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => {
            if (!r.ok) throw new Error('Unauthorized');
            return r.json();
        })
        .then(d => {
            this.user = d;
            this.organizations = d.organizations || [];
            this.loading = false;
        })
        .catch(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    }
}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1" x-show="user" x-text="'Welcome, ' + user?.name"></p>
    </div>

    <template x-if="loading">
        <div class="text-center py-12">
            <p class="text-gray-500">Loading...</p>
        </div>
    </template>

    <!-- Super Admin Panel -->
    <template x-if="!loading && user?.is_super_admin">
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white mb-6">
                <h2 class="text-2xl font-bold mb-2">Super Admin Panel</h2>
                <p class="text-blue-100">You have full access to manage the system</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Manage Organizations -->
                <a href="/super-admin/organizations"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 border-l-4 border-blue-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">Organizations</h3>
                    </div>
                    <p class="text-gray-600 mb-3">Create, edit, and manage all organizations</p>
                    <span class="text-blue-600 font-medium">Manage →</span>
                </a>

                <!-- Manage Super Admins -->
                <a href="/super-admin/admins"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 border-l-4 border-green-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">Super Admins</h3>
                    </div>
                    <p class="text-gray-600 mb-3">Add or remove super administrator accounts</p>
                    <span class="text-green-600 font-medium">Manage →</span>
                </a>

                <!-- System Stats -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-800">System</h3>
                    </div>
                    <p class="text-gray-600 mb-2">Total Organizations</p>
                    <p class="text-3xl font-bold text-purple-600" x-text="organizations.length"></p>
                </div>
            </div>
        </div>
    </template>

    <!-- Organization Admin Panel -->
    <template x-if="!loading && !user?.is_super_admin && organizations.length > 0">
        <div>
            <h2 class="text-2xl font-bold mb-4">Your Organizations</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="org in organizations" :key="org.id">
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold mb-1" x-text="org.name"></h3>
                            <p class="text-sm text-gray-500" x-text="org.slug"></p>
                            <span class="inline-block mt-2 px-2 py-1 text-xs rounded"
                                :class="org.role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                x-text="org.role.charAt(0).toUpperCase() + org.role.slice(1)"></span>
                        </div>

                        <!-- Admin Actions -->
                        <template x-if="org.role === 'admin'">
                            <div class="space-y-2">
                                <a :href="`/organizations/${org.slug}/contents`"
                                    class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center text-sm">
                                    Manage Contents
                                </a>
                                <a :href="`/organizations/${org.slug}/playlists`"
                                    class="block px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-center text-sm">
                                    Manage Playlists
                                </a>
                                <a :href="`/organizations/${org.slug}/users`"
                                    class="block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-center text-sm">
                                    Manage Users
                                </a>
                                <a :href="`/display/${org.slug}`" target="_blank"
                                    class="block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-center text-sm">
                                    View Display
                                </a>
                            </div>
                        </template>

                        <!-- User (Read-only) Actions -->
                        <template x-if="org.role === 'user'">
                            <div class="space-y-2">
                                <a :href="`/organizations/${org.slug}/view-contents`"
                                    class="block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-center text-sm">
                                    View Contents
                                </a>
                                <a :href="`/display/${org.slug}`" target="_blank"
                                    class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center text-sm">
                                    View Display
                                </a>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- No Organizations -->
    <template x-if="!loading && !user?.is_super_admin && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-500 mb-4">You don't have access to any organizations yet</p>
            <p class="text-sm text-gray-400">Contact your administrator to get access</p>
        </div>
    </template>
</div>
@endsection
