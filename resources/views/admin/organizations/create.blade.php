@extends('layouts.app')

@section('title', 'Tambah Organization')

@section('content')
<div class="max-w-3xl mx-auto px-4" x-data="{
    name: '',
    slug: '',
    domain: '',
    admin_email: '',
    admin_name: '',
    admin_password: '',
    loading: false,
    error: '',
    token: localStorage.getItem('token'),
    init() {
        if (!this.token) {
            window.location.href = '/login';
        }
    },
    generateSlug() {
        this.slug = this.name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    },
    submit() {
        this.error = '';
        this.loading = true;
        
        fetch('/api/admin/organizations', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: this.name,
                slug: this.slug,
                domain: this.domain,
                is_active: true
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.id) {
                window.location.href = '/admin/organizations';
            } else {
                this.error = d.message || 'Failed to create organization';
            }
        })
        .catch(() => this.error = 'Network error')
        .finally(() => this.loading = false);
    }
}">
    <div class="mb-6">
        <a href="/admin/organizations" class="text-blue-600 hover:underline">← Back to Organizations</a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6">Tambah Organization</h2>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded" x-text="error"></div>
        </template>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Nama Organization</label>
                <input type="text" x-model="name" @input="generateSlug()" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Slug</label>
                <input type="text" x-model="slug" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Auto-generated dari nama</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Domain (optional)</label>
                <input type="text" x-model="domain"
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="example.com">
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!loading">Simpan</span>
                    <span x-show="loading">Loading...</span>
                </button>
                <a href="/admin/organizations"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
