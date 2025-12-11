@extends('layouts.app')

@section('title', 'Tambah Content')

@section('content')
<div class="max-w-3xl mx-auto px-4" x-data="{
    title: '',
    description: '',
    type: 'image',
    duration: 10,
    file: null,
    thumbnail: null,
    is_active: true,
    loading: false,
    error: '',
    token: localStorage.getItem('token'),
    orgSlug: '{{ $organization }}',
    submit() {
        if (!this.file) {
            this.error = 'File wajib diisi';
            return;
        }
        this.error = '';
        this.loading = true;
        
        const formData = new FormData();
        formData.append('title', this.title);
        formData.append('description', this.description);
        formData.append('type', this.type);
        formData.append('duration', this.duration);
        formData.append('file', this.file);
        formData.append('is_active', this.is_active);
        if (this.thumbnail) formData.append('thumbnail', this.thumbnail);
        
        fetch(`/api/organizations/${this.orgSlug}/contents`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + this.token },
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            if (d.id) {
                window.location.href = `/${this.orgSlug}/contents`;
            } else {
                this.error = d.message || 'Failed';
            }
        })
        .catch(() => this.error = 'Network error')
        .finally(() => this.loading = false);
    }
}">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6">Tambah Content</h2>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded" x-text="error"></div>
        </template>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Title</label>
                <input type="text" x-model="title" required
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea x-model="description" rows="3"
                    class="w-full px-3 py-2 border rounded"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Type</label>
                <select x-model="type" class="w-full px-3 py-2 border rounded">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Duration (seconds)</label>
                <input type="number" x-model="duration" required min="1"
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">File</label>
                <input type="file" @change="file = $event.target.files[0]" required
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Thumbnail (optional)</label>
                <input type="file" @change="thumbnail = $event.target.files[0]" accept="image/*"
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" x-model="is_active" class="mr-2">
                    <span class="text-sm">Active</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!loading">Simpan</span>
                    <span x-show="loading">Loading...</span>
                </button>
                <a :href="`/${orgSlug}/contents`"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
