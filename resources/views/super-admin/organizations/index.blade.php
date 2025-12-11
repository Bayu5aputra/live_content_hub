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
    }
}">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Super Admin - Organizations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all organizations in the system</p>
        </div>
        <a href="/super-admin/organizations/create"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Create Organization
        </a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading && organizations.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">No organizations yet. Create your first one!</p>
        </div>
    </template>

    <template x-if="!loading && organizations.length > 0">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="org in organizations" :key="org.id">
                        <tr>
                            <td class="px-6 py-4 font-medium" x-text="org.name"></td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="org.slug"></td>
                            <td class="px-6 py-4">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded" x-text="org.code"></code>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="org.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                    x-text="org.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="px-6 py-4 text-sm" x-text="org.users_count || 0"></td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a :href="`/super-admin/organizations/${org.slug}/edit`"
                                    class="text-blue-600 hover:text-blue-800">Edit</a>
                                <button @click="deleteOrg(org.slug)"
                                    class="text-red-600 hover:text-red-800">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
@endsection
