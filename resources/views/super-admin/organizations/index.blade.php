@extends('layouts.app')

@section('title', 'Super Admin - Organizations')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    organizations: [],
    loading: true,
    showCreate: false,
    showDetails: false,
    selectedOrg: null,
    token: localStorage.getItem('token'),
    newOrg: {
        name: '',
        slug: '',
        domain: '',
        admin_name: '',
        admin_email: '',
        admin_password: ''
    },
    
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
    
    generateSlug() {
        this.newOrg.slug = this.newOrg.name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    },
    
    createOrg() {
        fetch('/api/super-admin/organizations', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.newOrg)
        })
        .then(r => r.json())
        .then(d => {
            if (d.data && d.data.id) {
                this.showCreate = false;
                this.newOrg = { name: '', slug: '', domain: '', admin_name: '', admin_email: '', admin_password: '' };
                this.loadOrganizations();
                alert('Organization created successfully!');
            } else if (d.errors) {
                alert(Object.values(d.errors).flat().join(', '));
            } else {
                alert(d.message || 'Failed to create organization');
            }
        });
    },
    
    viewDetails(org) {
        fetch(`/api/super-admin/organizations/${org.slug}`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            this.selectedOrg = d.data;
            this.showDetails = true;
        });
    },
    
    toggleStatus(slug) {
        fetch(`/api/super-admin/organizations/${slug}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(() => this.loadOrganizations());
    },
    
    deleteOrg(slug, name) {
        if (!confirm(`Are you sure you want to delete organization \"${name}\"? This will delete ALL contents, playlists, and users associated with it. This action cannot be undone!`)) return;

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

    copyCode(code) {
        navigator.clipboard.writeText(code);
        alert('Organization code copied to clipboard!');
    }
}">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Organizations Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage all organizations in the system</p>
            </div>
            <div class="flex gap-3">
                <a href="/super-admin/admins"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Manage Admins
                </a>
                <button @click="showCreate = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Organization
                </button>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
        <div @click.outside="showCreate = false" class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Create New Organization</h2>
            <form @submit.prevent="createOrg">
                <!-- Organization Info -->
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold mb-3">Organization Information</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Organization Name *</label>
                        <input type="text" x-model="newOrg.name" @input="generateSlug()" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Slug *</label>
                        <input type="text" x-model="newOrg.slug" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Auto-generated from name (URL-friendly)</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Domain (optional)</label>
                        <input type="text" x-model="newOrg.domain" placeholder="example.com"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Admin Account -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3">Admin Account</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Admin Name *</label>
                        <input type="text" x-model="newOrg.admin_name" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Admin Email *</label>
                        <input type="email" x-model="newOrg.admin_email" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Admin Password *</label>
                        <input type="password" x-model="newOrg.admin_password" required minlength="8"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Organization
                    </button>
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Details Modal -->
    <div x-show="showDetails" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
        <div @click.outside="showDetails = false" class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <template x-if="selectedOrg">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold" x-text="selectedOrg.name"></h2>
                        <button @click="showDetails = false" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-blue-600 font-medium">Total Users</p>
                            <p class="text-2xl font-bold text-blue-900" x-text="selectedOrg.users_count || 0"></p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-600 font-medium">Total Contents</p>
                            <p class="text-2xl font-bold text-green-900" x-text="selectedOrg.contents_count || 0"></p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-purple-600 font-medium">Total Playlists</p>
                            <p class="text-2xl font-bold text-purple-900" x-text="selectedOrg.playlists_count || 0"></p>
                        </div>
                    </div>

                    <!-- Organization Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold mb-3">Organization Details</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Slug:</span>
                                <span class="font-medium" x-text="selectedOrg.slug"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Domain:</span>
                                <span class="font-medium" x-text="selectedOrg.domain || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Code:</span>
                                <div class="flex items-center gap-2">
                                    <code class="bg-white px-2 py-1 rounded border font-mono text-xs" x-text="selectedOrg.code"></code>
                                    <button @click="copyCode(selectedOrg.code)" class="text-blue-600 hover:text-blue-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span :class="selectedOrg.is_active ? 'text-green-600' : 'text-red-600'"
                                    class="font-medium" x-text="selectedOrg.is_active ? 'Active' : 'Inactive'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium" x-text="new Date(selectedOrg.created_at).toLocaleDateString()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Accounts -->
                    <div class="mb-4">
                        <h3 class="font-semibold mb-3">Admin Accounts</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <template x-if="selectedOrg.users && selectedOrg.users.length > 0">
                                <div class="space-y-2">
                                    <template x-for="user in selectedOrg.users" :key="user.id">
                                        <div class="flex items-center justify-between py-2 border-b last:border-0">
                                            <div>
                                                <p class="font-medium" x-text="user.name"></p>
                                                <p class="text-sm text-gray-600" x-text="user.email"></p>
                                            </div>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium"
                                                x-text="user.pivot.role"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!selectedOrg.users || selectedOrg.users.length === 0">
                                <p class="text-gray-500 text-sm">No admin accounts found</p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">Loading organizations...</p>
        </div>
    </template>

    <!-- Organizations Table -->
    <template x-if="!loading && organizations.length > 0">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="org in organizations" :key="org.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900" x-text="org.name"></p>
                                    <p class="text-sm text-gray-500" x-text="org.slug"></p>
                                    <p class="text-xs text-gray-400" x-text="org.domain || 'No domain'"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono" x-text="org.code"></code>
                                    <button @click="copyCode(org.code)" class="text-blue-600 hover:text-blue-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-3">
                                    <span class="text-gray-600" x-text="`${org.users_count || 0} users`"></span>
                                    <span class="text-gray-600" x-text="`${org.contents_count || 0} contents`"></span>
                                    <span class="text-gray-600" x-text="`${org.playlists_count || 0} playlists`"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="toggleStatus(org.slug)"
                                    :class="org.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                                    class="px-3 py-1 rounded-full text-xs font-medium transition">
                                    <span x-text="org.is_active ? 'Active' : 'Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="viewDetails(org)"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        View Details
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button @click="deleteOrg(org.slug, org.name)"
                                        class="text-red-600 hover:text-red-800 font-medium">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-gray-500 mb-4">No organizations yet</p>
            <button @click="showCreate = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Create Your First Organization
            </button>
        </div>
    </template>

</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
