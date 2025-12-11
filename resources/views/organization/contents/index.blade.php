@extends('layouts.app')

@section('title', 'Contents')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    contents: [],
    loading: true,
    token: localStorage.getItem('token'),
    orgSlug: '{{ $organization }}',
    init() {
        if (!this.token) { window.location.href = '/login'; return; }
        this.loadContents();
    },
    loadContents() {
        fetch(`/api/organizations/${this.orgSlug}/contents`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.contents = d.data || [])
        .finally(() => this.loading = false);
    },
    deleteContent(id) {
        if (!confirm('Hapus content ini?')) return;
        fetch(`/api/organizations/${this.orgSlug}/contents/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => this.loadContents());
    },
    toggleStatus(id) {
        fetch(`/api/organizations/${this.orgSlug}/contents/${id}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => this.loadContents());
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Contents</h1>
        <a :href="`/${orgSlug}/contents/create`"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Tambah Content
        </a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="content in contents" :key="content.id">
                        <tr>
                            <td class="px-6 py-4" x-text="content.title"></td>
                            <td class="px-6 py-4" x-text="content.type"></td>
                            <td class="px-6 py-4" x-text="content.duration + 's'"></td>
                            <td class="px-6 py-4">
                                <button @click="toggleStatus(content.id)"
                                    :class="content.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 py-1 rounded text-xs">
                                    <span x-text="content.is_active ? 'Active' : 'Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="deleteContent(content.id)"
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
