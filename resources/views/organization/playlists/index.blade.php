@extends('layouts.app')

@section('title', 'Playlists')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    playlists: [],
    loading: true,
    token: localStorage.getItem('token'),
    orgSlug: '{{ $organization }}',
    init() {
        if (!this.token) { window.location.href = '/login'; return; }
        this.loadPlaylists();
    },
    loadPlaylists() {
        fetch(`/api/organizations/${this.orgSlug}/playlists`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.playlists = d.data || [])
        .finally(() => this.loading = false);
    },
    deletePlaylist(id) {
        if (!confirm('Hapus playlist ini?')) return;
        fetch(`/api/organizations/${this.orgSlug}/playlists/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => this.loadPlaylists());
    },
    toggleStatus(id) {
        fetch(`/api/organizations/${this.orgSlug}/playlists/${id}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => this.loadPlaylists());
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Playlists</h1>
        <a :href="`/${orgSlug}/playlists/create`"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Tambah Playlist
        </a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="playlist in playlists" :key="playlist.id">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold mb-2" x-text="playlist.name"></h3>
                    <p class="text-sm text-gray-500 mb-4" x-text="playlist.description"></p>
                    <div class="flex items-center gap-2 mb-4">
                        <button @click="toggleStatus(playlist.id)"
                            :class="playlist.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="px-2 py-1 rounded text-xs">
                            <span x-text="playlist.is_active ? 'Active' : 'Inactive'"></span>
                        </button>
                        <span class="text-xs text-gray-500" x-text="`${playlist.contents_count || 0} contents`"></span>
                    </div>
                    <div class="flex gap-2">
                        <a :href="`/${orgSlug}/playlists/${playlist.id}`"
                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Manage
                        </a>
                        <button @click="deletePlaylist(playlist.id)"
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection
