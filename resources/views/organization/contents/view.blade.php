@extends('layouts.app')

@section('title', 'View Contents')

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
        fetch(`/api/organizations/${this.orgSlug}/contents-view`, {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.contents = d.data || [])
        .finally(() => this.loading = false);
    }
}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Contents</h1>
        <p class="text-sm text-gray-500 mt-1">View organization contents (Read-only)</p>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading && contents.length === 0">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">No contents available</p>
        </div>
    </template>

    <template x-if="!loading && contents.length > 0">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="content in contents" :key="content.id">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gray-200 relative">
                        <template x-if="content.thumbnail_url">
                            <img :src="content.thumbnail_url" :alt="content.title"
                                class="w-full h-full object-cover">
                        </template>
                        <template x-if="!content.thumbnail_url">
                            <div class="flex items-center justify-center h-full">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </template>

                        <!-- Type Badge -->
                        <span class="absolute top-2 right-2 px-2 py-1 bg-black/70 text-white text-xs rounded"
                            x-text="content.type.toUpperCase()"></span>
                    </div>

                    <!-- Content Info -->
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-2" x-text="content.title"></h3>
                        <p class="text-sm text-gray-600 mb-3" x-text="content.description || 'No description'"></p>

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span x-text="`Duration: ${content.duration}s`"></span>
                            <span class="px-2 py-1 rounded text-xs"
                                :class="content.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                x-text="content.is_active ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection
