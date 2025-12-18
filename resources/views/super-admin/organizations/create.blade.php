@extends('layouts.app')

@section('title', 'Create Organization')

@section('content')
<div class="max-w-3xl mx-auto px-4" x-data="{
    name: '',
    slug: '',
    domain: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
    loading: false,
    error: '',
    success: '',
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
        this.success = '';
        this.loading = true;
        
        fetch('/api/super-admin/organizations', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: this.name,
                slug: this.slug,
                domain: this.domain,
                admin_name: this.admin_name,
                admin_email: this.admin_email,
                admin_password: this.admin_password,
                is_active: true
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.data && d.data.id) {
                this.success = 'Organization created successfully!';
                setTimeout(() => {
                    window.location.href = '/super-admin/organizations';
                }, 1500);
            } else if (d.errors) {
                this.error = Object.values(d.errors).flat().join(', ');
            } else {
                this.error = d.message || 'Failed to create organization';
            }
        })
        .catch(() => this.error = 'Network error. Please try again.')
        .finally(() => this.loading = false);
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

    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold mb-2">Create New Organization</h2>
        <p class="text-gray-600 mb-6">Fill in the details below to create a new organization</p>

        <template x-if="error">
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" x-text="error"></div>
        </template>

        <template x-if="success">
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" x-text="success"></div>
        </template>

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <!-- Organization Details -->
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold mb-4">Organization Details</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Organization Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="name" @input="generateSlug()" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="slug" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Auto-generated from name. Used in URL.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Domain (optional)
                        </label>
                        <input type="text" x-model="domain"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="example.com">
                        <p class="text-xs text-gray-500 mt-1">Custom domain for this organization.</p>
                    </div>
                </div>

                <!-- Admin Account -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Organization Admin Account</h3>
                    <p class="text-sm text-gray-600 mb-4">Create the first admin account for this organization.</p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="admin_name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" x-model="admin_email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" x-model="admin_password" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button type="submit" :disabled="loading"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium">
                    <span x-show="!loading">Create Organization</span>
                    <span x-show="loading">Creating...</span>
                </button>
                <a href="/super-admin/organizations"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
