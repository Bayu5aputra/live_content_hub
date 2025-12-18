@extends('layouts.app')

@section('title', 'View Organization')

@section('content')
<div class="max-w-4xl mx-auto px-4" x-data="{
    organization: null,
    loading: true,
    token: localStorage.getItem('token'),
    slug: '{{ $slug }}',
    init() {
        if (!this.token) {
            window.location.href = '/login';
            return;
        }
        this.loadOrganization();
    },
    loadOrganization() {
        fetch(`/api/super-admin/organizations/${this.slug}`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            this.organization = d.data || d;
        })
        .finally(() => this.loading = false);
    },
    copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Copied to clipboard!');
    }
}">
    <div class="mb-6">
        <a href="/super-admin/organizations" class="text-blue-600 hover:underline flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Organizations
        </a>
    </div>

    <template x-if="loading">
        <div class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading...</p>
        </div>
    </template>

    <template x-if="!loading && organization">
        <div>
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-3xl font-bold" x-text="organization.name"></h2>
                        <p class="text-gray-500 mt-1" x-text="organization.slug"></p>
                    </div>
                    <span :class="organization.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        class="px-3 py-1 rounded-full text-sm font-semibold">
                        <span x-text="organization.is_active ? 'Active' : 'Inactive'"></span>
                    </span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Organization Code</label>
                        <div class="flex items-center gap-2">
                            <code class="text-lg font-mono bg-gray-100 px-4 py-2 rounded" x-text="organization.code"></code>
                            <button @click="copyToClipboard(organization.code)"
                                class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Domain</label>
                        <p class="text-lg" x-text="organization.domain || 'Not set'"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Total Users</label>
                        <p class="text-lg font-semibold" x-text="organization.users_count || 0"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Total Contents</label>
                        <p class="text-lg font-semibold" x-text="organization.contents_count || 0"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8">
                <h3 class="text-xl font-bold mb-4">Organization Admins</h3>
                <template x-if="organization.users && organization.users.length > 0">
                    <div class="space-y-3">
                        <template x-for="user in organization.users.filter(u => u.role === 'admin')" :key="user.id">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium" x-text="user.name"></p>
                                        <p class="text-sm text-gray-500" x-text="user.email"></p>
                                    </div>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                        Admin
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!organization.users || organization.users.length === 0">
                    <p class="text-gray-500">No admins yet</p>
                </template>
            </div>

            <div class="mt-6 flex gap-3">
                <a :href="`/super-admin/organizations/${slug}/edit`"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                    Edit Organization
                </a>
                <a :href="`/display/${organization.slug}`" target="_blank"
                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                    View Display
                </a>
            </div>
        </div>
    </template>
</div>
@endsection
