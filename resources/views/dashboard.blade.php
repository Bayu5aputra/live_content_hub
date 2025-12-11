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
            
            // Redirect berdasarkan role
            if (d.is_super_admin) {
                window.location.href = '/super-admin/organizations';
            }
        })
        .catch(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        })
        .finally(() => this.loading = false);
    }
}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Dashboard</h1>
    </div>

    <template x-if="loading">
        <div class="text-center py-12">
            <p class="text-gray-500">Loading...</p>
        </div>
    </template>

    <template x-if="!loading && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500 mb-4">Anda belum memiliki organisasi</p>
        </div>
    </template>

    <template x-if="!loading && organizations.length > 0">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="org in organizations" :key="org.id">
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-2" x-text="org.name"></h3>
                    <p class="text-sm text-gray-500 mb-1" x-text="org.slug"></p>
                    <p class="text-xs text-gray-400 mb-4" x-text="'Role: ' + org.role"></p>
                    <div class="flex gap-2">
                        <a :href="`/organizations/${org.slug}/contents`"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Manage Contents
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection
