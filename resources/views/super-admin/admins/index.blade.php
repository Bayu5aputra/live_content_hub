@extends('layouts.app')

@section('title', 'Manage Super Admins')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="{
    admins: [],
    loading: true,
    showCreate: false,
    showPassword: false,
    editingId: null,
    token: localStorage.getItem('token'),
    currentUserId: null,
    newAdmin: { name: '', email: '', password: '' },
    passwordData: { user_id: null, password: '', user_name: '' },
    
    init() {
        if (!this.token) { 
            window.location.href = '/login'; 
            return; 
        }
        this.checkAuth();
    },
    
    checkAuth() {
        fetch('/api/user', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            if (!d.is_super_admin) {
                window.location.href = '/dashboard';
                return;
            }
            this.currentUserId = d.id;
            this.loadAdmins();
        });
    },
    
    loadAdmins() {
        fetch('/api/super-admin/admins', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => this.admins = d.data || [])
        .finally(() => this.loading = false);
    },
    
    createAdmin() {
        if (this.newAdmin.password.length < 8) {
            alert('Password must be at least 8 characters');
            return;
        }
        
        fetch('/api/super-admin/admins', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.newAdmin)
        })
        .then(r => r.json())
        .then(d => {
            if (d.data) {
                this.showCreate = false;
                this.newAdmin = { name: '', email: '', password: '' };
                this.loadAdmins();
                alert('Super admin created successfully!');
            } else if (d.errors) {
                alert(Object.values(d.errors).flat().join(', '));
            } else {
                alert(d.message || 'Failed to create super admin');
            }
        });
    },
    
    openPasswordModal(userId, userName) {
        this.passwordData = { user_id: userId, password: '', user_name: userName };
        this.showPassword = true;
    },
    
    updatePassword() {
        if (this.passwordData.password.length < 8) {
            alert('Password must be at least 8 characters');
            return;
        }
        
        fetch(`/api/super-admin/admins/${this.passwordData.user_id}/password`, {
            method: 'PATCH',
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ password: this.passwordData.password })
        })
        .then(r => r.json())
        .then(d => {
            this.showPassword = false;
            alert('Password updated successfully!');
        });
    },
    
    deleteAdmin(id, name) {
        if (id === this.currentUserId) {
            alert('You cannot delete yourself!');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete super admin \"${name}\"? This action cannot be undone!`)) return;

        fetch(`/api/super-admin/admins/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(r => r.json())
        .then(d => {
            if (d.message) {
                this.loadAdmins();
                alert(d.message);
            }
        })
        .catch(() => alert('Failed to delete super admin'));
    }
}">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Super Admins Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage super administrator accounts</p>
            </div>
            <div class="flex gap-3">
                <a href="/super-admin/organizations"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Back to Organizations
                </a>
                <button @click="showCreate = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Super Admin
                </button>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
        <div @click.outside="showCreate = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Add Super Admin</h2>
            <form @submit.prevent="createAdmin">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Name *</label>
                    <input type="text" x-model="newAdmin.name" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Email *</label>
                    <input type="email" x-model="newAdmin.email" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Password *</label>
                    <input type="password" x-model="newAdmin.password" required minlength="8"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Create
                    </button>
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div x-show="showPassword" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
        <div @click.outside="showPassword = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-2">Change Password</h2>
            <p class="text-sm text-gray-600 mb-4" x-text="`Update password for: ${passwordData.user_name}`"></p>

            <form @submit.prevent="updatePassword">
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">New Password *</label>
                    <input type="password" x-model="passwordData.password" required minlength="8"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Password
                    </button>
                    <button type="button" @click="showPassword = false"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="mt-4 text-gray-600">Loading super admins...</p>
        </div>
    </template>

    <!-- Admins Table -->
    <template x-if="!loading && admins.length > 0">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="admin in admins" :key="admin.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900" x-text="admin.name"></p>
                                    <span x-show="admin.id === currentUserId"
                                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        You
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="admin.email"></td>
                            <td class="px-6 py-4 text-sm text-gray-600"
                                x-text="new Date(admin.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="openPasswordModal(admin.id, admin.name)"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        Change Password
                                    </button>
                                    <template x-if="admin.id !== currentUserId">
                                        <span class="text-gray-300">|</span>
                                    </template>
                                    <template x-if="admin.id !== currentUserId">
                                        <button @click="deleteAdmin(admin.id, admin.name)"
                                            class="text-red-600 hover:text-red-800 font-medium">
                                            Delete
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && admins.length === 0">
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-gray-500 mb-4">No super admins found</p>
            <button @click="showCreate = true"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Add First Super Admin
            </button>
        </div>
    </template>

</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
