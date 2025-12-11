@extends('layouts.app')

@section('title', 'Tambah Playlist')

@section('content')
<div class="max-w-3xl mx-auto px-4" x-data="{
    name: '',
    description: '',
    is_active: true,
    loop: true,
    loading: false,
    error: '',
    token: localStorage.getItem('token'),
    orgSlug: '{{ $organization }}',
    submit() {
        this.error = '';
        this.loading = true;
        
        fetch(`/api/organizations/${this.orgSlug}/playlists`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: this.name,
                description: this.description,
                is_active: this.is_active,
                loop: this.loop
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.id) {
                window.location.href = `/${this.orgSlug}/playlists`;
            } else {
                this.error = d.message || 'Failed';
            }
        })
        .catch(() => this.error = 'Network error')
        .finally(() => this.loading = false);
    }
}">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6">Tambah Playlist</h2>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded" x-text="error"></div>
        </template>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Nama Playlist</label>
                <input type="text" x-model="name" required
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea x-model="description" rows="3"
                    class="w-full px-3 py-2 border rounded"></textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" x-model="is_active" class="mr-2">
                    <span class="text-sm">Active</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" x-model="loop" class="mr-2">
                    <span class="text-sm">Loop</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!loading">Simpan</span>
                    <span x-show="loading">Loading...</span>
                </button>
                <a :href="`/${orgSlug}/playlists`"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
