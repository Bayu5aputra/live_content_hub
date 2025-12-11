@extends('layouts.app')

@section('title', 'Manage Playlist')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    playlist: null,
    availableContents: [],
    loading: true,
    token: localStorage.getItem('token'),
    orgSlug: '{{ $organization }}',
    playlistId: '{{ $playlist }}',
    init() {
        if (!this.token) { window.location.href = '/login'; return; }
        this.loadPlaylist();
        this.loadAvailableContents();
    },
    loadPlaylist() {
        fetch(`/api/organizations/${this.orgSlug}/playlists/${this.playlistId}`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.playlist = d)
        .finally(() => this.loading = false);
    },
    loadAvailableContents() {
        fetch(`/api/organizations/${this.orgSlug}/contents`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.availableContents = d.data || []);
    },
    addContent(contentId) {
        fetch(`/api/organizations/${this.orgSlug}/playlists/${this.playlistId}/contents`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content_id: contentId })
        }).then(() => {
            this.loadPlaylist();
            this.loadAvailableContents();
        });
    },
    removeContent(contentId) {
        if (!confirm('Hapus content dari playlist?')) return;
        fetch(`/api/organizations/${this.orgSlug}/playlists/${this.playlistId}/contents/${contentId}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).then(() => {
            this.loadPlaylist();
            this.loadAvailableContents();
        });
    }
}">
    <div class="mb-6">
        <a :href="`/${orgSlug}/playlists`" class="text-blue-600 hover:underline">← Back to Playlists</a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading && playlist">
        <div>
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h1 class="text-3xl font-bold mb-2" x-text="playlist.name"></h1>
                <p class="text-gray-600" x-text="playlist.description"></p>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Current Contents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Contents in Playlist</h2>
                    <div class="space-y-2">
                        <template x-for="content in playlist.contents" :key="content.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium" x-text="content.title"></p>
                                    <p class="text-sm text-gray-500" x-text="content.type"></p>
                                </div>
                                <button @click="removeContent(content.id)"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    Remove
                                </button>
                            </div>
                        </template>
                        <template x-if="!playlist.contents || playlist.contents.length === 0">
                            <p class="text-gray-500 text-center py-8">Belum ada content</p>
                        </template>
                    </div>
                </div>

                <!-- Available Contents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Available Contents</h2>
                    <div class="space-y-2">
                        <template x-for="content in availableContents.filter(c => !playlist.contents.find(pc => pc.id === c.id))" :key="content.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium" x-text="content.title"></p>
                                    <p class="text-sm text-gray-500" x-text="content.type"></p>
                                </div>
                                <button @click="addContent(content.id)"
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Add
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
