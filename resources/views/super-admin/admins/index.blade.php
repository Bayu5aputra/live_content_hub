@extends('layouts.app')

@section('title', 'Super Admin - Organizations')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    organizations: [],
    loading: true,
    token: localStorage.getItem('token'),
    init() {
        if (!this.token) { 
            window.location.href = '/login'; 
            return; 
        }
        this.checkSuperAdmin();
    },
    checkSuperAdmin() {
        fetch('/api/user', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            if (!d.is_super_admin) {
                window.location.href = '/dashboard';
                return;
            }
            this.loadOrganizations();
        })
        .catch(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    },
    loadOrganizations() {
        fetch('/api/super-admin/organizations', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            this.organizations = d.data || [];
        })
        .finally(() => this.loading = false);
    },
    deleteOrg(slug) {
        if (!confirm('Hapus organisasi ini?')) return;
        
        fetch(`/api/super-admin/organizations/${slug}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(() => {
            this.loadOrganizations();
            alert('Organization deleted successfully!');
        })
        .catch(err => alert('Failed to delete organization'));
    },
    toggleStatus(slug) {
        fetch(`/api/super-admin/organizations/${slug}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(() => this.loadOrganizations())
        .catch(err => alert('Failed to toggle status'));
    }
}">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Super Admin - Organizations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all organizations in the system</p>
        </div>
        <a href="/super-admin/organizations/create"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            + Create Organization
        </a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading...</p>
        </div>
    </template>

    <template x-if="!loading && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-500 mb-4">No organizations yet</p>
            <a href="/super-admin/organizations/create"
                class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Create Your First Organization
            </a>
        </div>
    </template>

    <template x-if="!loading && organizations.length > 0">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="org in organizations" :key="org.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900" x-text="org.name"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-sm text-gray-500" x-text="org.slug"></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono" x-text="org.code"></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button @click="toggleStatus(org.slug)"
                                    :class="org.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold transition">
                                    <span x-text="org.is_active ? 'Active' : 'Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="org.users_count || 0"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="org.contents_count || 0"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                                <a :href="`/super-admin/organizations/${org.slug}/view`"
                                    class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                <a :href="`/super-admin/organizations/${org.slug}/edit`"
                                    class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                <button @click="deleteOrg(org.slug)"
                                    class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
@endsection
