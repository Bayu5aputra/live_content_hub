@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    user: null,
    stats: {
        total_organizations: 0,
        active_organizations: 0,
        total_super_admins: 0,
        total_contents: 0
    },
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
            
            // Load stats for Super Admin
            if (d.is_super_admin) {
                this.loadSuperAdminStats();
            } else {
                // Redirect non-super-admin
                this.loading = false;
            }
        })
        .catch(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    },
    
    loadSuperAdminStats() {
        fetch('/api/super-admin/stats', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            this.stats = d;
            this.loading = false;
        })
        .catch(err => {
            console.error('Failed to load stats:', err);
            this.loading = false;
        });
    }
}">

    <template x-if="loading">
        <div class="text-center py-12">
            <p class="text-gray-500">Loading...</p>
        </div>
    </template>

    <!-- Super Admin Dashboard -->
    <template x-if="!loading && user?.is_super_admin">
        <div>
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Super Admin Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Welcome back, <span x-text="user?.name"></span>
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Organizations -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Organizations</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="stats.total_organizations"></p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-sm text-green-600 dark:text-green-400" x-text="stats.active_organizations + ' active'"></span>
                    </div>
                </div>

                <!-- Super Admins -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Super Admins</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="stats.total_super_admins"></p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Contents -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Contents</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="stats.total_contents"></p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                    <p class="text-sm opacity-90 mb-2">Quick Actions</p>
                    <div class="space-y-2 mt-4">
                        <a href="/super-admin/organizations/create"
                           class="block w-full text-center bg-white text-blue-600 px-4 py-2 rounded hover:bg-blue-50 transition text-sm font-medium">
                            + New Organization
                        </a>
                        <a href="/super-admin/admins"
                           class="block w-full text-center bg-blue-400 text-white px-4 py-2 rounded hover:bg-blue-500 transition text-sm font-medium">
                            Manage Admins
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Organizations -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Organizations</h2>
                        <a href="/super-admin/organizations"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 text-center py-8">
                        Go to <a href="/super-admin/organizations" class="text-blue-600 hover:underline">Organizations page</a> to manage all organizations
                    </p>
                </div>
            </div>
        </div>
    </template>

    <!-- Non-Super Admin (Organization Users) -->
    <template x-if="!loading && !user?.is_super_admin">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1" x-show="user" x-text="'Welcome, ' + user?.name"></p>
            </div>

            <!-- Organization List for Regular Users -->
            <template x-if="user?.organizations && user.organizations.length > 0">
                <div>
                    <h2 class="text-2xl font-bold mb-4">Your Organizations</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="org in user.organizations" :key="org.id">
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
                                        <a :href="`/organizations/${org.slug}/contents-readonly`"
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
            <template x-if="!user?.organizations || user.organizations.length === 0">
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-gray-500 mb-4">You don't have access to any organizations yet</p>
                    <p class="text-sm text-gray-400">Contact your administrator to get access</p>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection
