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
    newAdmin: { name: '', email: '', password: '' },
    passwordData: { user_id: null, password: '' },
    init() {
        if (!this.token) { window.location.href = '/login'; return; }
        this.loadAdmins();
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
            } else {
                alert(d.message || 'Failed to create super admin');
            }
        });
    },
    openPasswordModal(userId) {
        this.passwordData.user_id = userId;
        this.passwordData.password = '';
        this.showPassword = true;
    },
    updatePassword() {
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
    deleteAdmin(id) {
        if (!confirm('Delete this super admin?')) return;
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
        });
    }
}">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Manage Super Admins</h1>
            <p class="text-sm text-gray-500 mt-1">Add, edit, or remove super admin accounts</p>
        </div>
        <button @click="showCreate = true"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Add Super Admin
        </button>
    </div>

    <!-- Create Modal -->
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Add Super Admin</h2>
            <form @submit.prevent="createAdmin">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Name</label>
                    <input type="text" x-model="newAdmin.name" required
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" x-model="newAdmin.email" required
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" x-model="newAdmin.password" required minlength="8"
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Create
                    </button>
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div x-show="showPassword" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.outside="showPassword = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Change Password</h2>
            <form @submit.prevent="updatePassword">
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">New Password</label>
                    <input type="password" x-model="passwordData.password" required minlength="8"
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Update
                    </button>
                    <button type="button" @click="showPassword = false"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <template x-if="!loading">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="admin in admins" :key="admin.id">
                        <tr>
                            <td class="px-6 py-4 font-medium" x-text="admin.name"></td>
                            <td class="px-6 py-4 text-sm" x-text="admin.email"></td>
                            <td class="px-6 py-4 text-sm" x-text="new Date(admin.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button @click="openPasswordModal(admin.id)"
                                    class="text-blue-600 hover:text-blue-800">Change Password</button>
                                <button @click="deleteAdmin(admin.id)"
                                    class="text-red-600 hover:text-red-800">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
@endsection
