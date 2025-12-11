@extends('layouts.app')

@section('title', 'Admin - Organizations')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    organizations: [],
    loading: true,
    token: localStorage.getItem('token'),
    showCreate: false,
    newOrg: { name: '', slug: '', domain: '' },
    init() {
        if (!this.token) { window.location.href = '/login'; return; }
        this.loadOrganizations();
    },
    loadOrganizations() {
        fetch('/api/admin/organizations', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.organizations = d.data || [])
        .finally(() => this.loading = false);
    },
    createOrg() {
        fetch('/api/admin/organizations', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.newOrg)
        })
        .then(() => {
            this.showCreate = false;
            this.newOrg = { name: '', slug: '', domain: '' };
            this.loadOrganizations();
        });
    },
    deleteOrg(id) {
        if (!confirm('Hapus organisasi ini?')) return;
        fetch(`/api/admin/organizations/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => this.loadOrganizations());
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Organizations</h1>
        <button @click="showCreate = true"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Tambah Organization
        </button>
    </div>

    <!-- Create Modal -->
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Tambah Organization</h2>
            <form @submit.prevent="createOrg">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nama</label>
                    <input type="text" x-model="newOrg.name" required
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Slug</label>
                    <input type="text" x-model="newOrg.slug"
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Domain (optional)</label>
                    <input type="text" x-model="newOrg.domain"
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Simpan
                    </button>
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="org in organizations" :key="org.id">
                        <tr>
                            <td class="px-6 py-4" x-text="org.name"></td>
                            <td class="px-6 py-4" x-text="org.slug"></td>
                            <td class="px-6 py-4" x-text="org.users_count || 0"></td>
                            <td class="px-6 py-4" x-text="org.contents_count || 0"></td>
                            <td class="px-6 py-4">
                                <button @click="deleteOrg(org.id)"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
@endsection
