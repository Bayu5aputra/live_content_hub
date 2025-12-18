@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4"
     x-data="{
        user: null,
        stats: {
            total_organizations: 0,
            active_organizations: 0,
            total_super_admins: 0,
            total_contents: 0
        },
        loading: true,
        error: null,
        token: localStorage.getItem('token'),

        init() {
            console.log('Dashboard Init - Token:', this.token ? 'EXISTS' : 'MISSING');

            if (!this.token) {
                window.location.href = '/login';
                return;
            }

            this.loadUser();
        },

        /* =========================
         * LOAD AUTH USER
         * ========================= */
        loadUser() {
            fetch('/api/user', {
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Accept': 'application/json'
                }
            })
            .then(r => {
                if (!r.ok) throw new Error('Unauthorized');
                return r.json();
            })
            .then(res => {
                console.log('FULL RESPONSE:', res);

                /* =========================
                 * FIX UTAMA ADA DI SINI
                 * ========================= */
                const user = res.data;

                console.log('USER DATA:', user);
                console.log('is_super_admin:', user.is_super_admin);
                console.log('type:', typeof user.is_super_admin);

                this.user = user;

                if (this.isSuperAdmin()) {
                    console.log('User is Super Admin');
                    this.loadSuperAdminStats();
                } else {
                    console.log('User is NOT Super Admin');
                    this.loading = false;
                }
            })
            .catch(err => {
                console.error(err);
                localStorage.removeItem('token');
                window.location.href = '/login';
            });
        },

        /* =========================
         * LOAD SUPER ADMIN STATS
         * ========================= */
        loadSuperAdminStats() {
            fetch('/api/super-admin/stats', {
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Accept': 'application/json'
                }
            })
            .then(r => {
                if (!r.ok) throw new Error('Failed to load stats');
                return r.json();
            })
            .then(d => {
                console.log('STATS:', d);
                this.stats = d;
                this.loading = false;
            })
            .catch(() => {
                this.error = 'Failed to load statistics';
                this.loading = false;
            });
        },

        /* =========================
         * ROLE CHECK
         * ========================= */
        isSuperAdmin() {
            return !!(
                this.user &&
                (this.user.is_super_admin === true || this.user.is_super_admin === 1)
            );
        }
     }">

    <!-- LOADING -->
    <template x-if="loading">
        <div class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-500">Loading...</p>
        </div>
    </template>

    <!-- ERROR -->
    <template x-if="error">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>Error:</strong>
            <span x-text="error"></span>
        </div>
    </template>

    <!-- =========================
         SUPER ADMIN DASHBOARD
         ========================= -->
    <template x-if="!loading && isSuperAdmin()">
        <div>
            <h1 class="text-3xl font-bold mb-6">Super Admin Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Total Organizations</p>
                    <p class="text-3xl font-bold" x-text="stats.total_organizations"></p>
                    <p class="text-sm text-green-600" x-text="stats.active_organizations + ' active'"></p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Super Admins</p>
                    <p class="text-3xl font-bold" x-text="stats.total_super_admins"></p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Total Contents</p>
                    <p class="text-3xl font-bold" x-text="stats.total_contents"></p>
                </div>

                <div class="bg-blue-600 text-white p-6 rounded shadow">
                    <p class="text-sm opacity-80">Quick Actions</p>
                    <a href="/super-admin/organizations/create"
                       class="block mt-4 bg-white text-blue-600 text-center py-2 rounded font-semibold">
                        + New Organization
                    </a>
                </div>
            </div>
        </div>
    </template>

    <!-- =========================
         NON SUPER ADMIN
         ========================= -->
    <template x-if="!loading && !isSuperAdmin() && user">
        <div>
            <h1 class="text-3xl font-bold mb-2">Dashboard</h1>
            <p class="text-sm text-gray-500 mb-6">
                Welcome, <span x-text="user.name"></span>
            </p>

            <template x-if="user.organizations && user.organizations.length">
                <div class="grid md:grid-cols-3 gap-6">
                    <template x-for="org in user.organizations" :key="org.id">
                        <div class="bg-white p-6 rounded shadow">
                            <h3 class="text-xl font-semibold" x-text="org.name"></h3>
                            <p class="text-sm text-gray-500" x-text="org.slug"></p>

                            <span class="inline-block mt-2 px-2 py-1 text-xs rounded"
                                  :class="org.role === 'admin'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800'"
                                  x-text="org.role">
                            </span>

                            <div class="mt-4 space-y-2">
                                <a :href="`/display/${org.slug}`"
                                   target="_blank"
                                   class="block text-center bg-gray-700 text-white py-2 rounded">
                                    View Display
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!user.organizations || !user.organizations.length">
                <div class="bg-white p-8 rounded shadow text-center">
                    <p class="text-gray-500">You don’t have access to any organizations.</p>
                </div>
            </template>
        </div>
    </template>

</div>
@endsection
