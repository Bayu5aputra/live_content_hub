@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="max-w-7xl mx-auto px-4"
     x-data="{
        users: [],
        loading: true,
        showCreate: false,
        showPassword: false,
        token: localStorage.getItem('token'),
        orgSlug: '{{ $organization }}',
        newUser: { name: '', email: '', password: '', role: 'user' },
        passwordData: { user_id: null, password: '' },

        init() {
            if (!this.token) {
                window.location.href = '/login';
                return;
            }
            this.loadUsers();
        },

        loadUsers() {
            fetch(`/api/organizations/${this.orgSlug}/users`, {
                headers: { 'Authorization': 'Bearer ' + this.token }
            })
            .then(r => r.json())
            .then(d => this.users = d.data || [])
            .finally(() => this.loading = false);
        },

        createUser() {
            fetch(`/api/organizations/${this.orgSlug}/users`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.newUser)
            })
            .then(r => r.json())
            .then(d => {
                if (d.data) {
                    this.showCreate = false;
                    this.newUser = { name: '', email: '', password: '', role: 'user' };
                    this.loadUsers();
                    alert('User added successfully!');
                } else {
                    alert(d.message || 'Failed to add user');
                }
            });
        },

        updateRole(userId, newRole) {
            fetch(`/api/organizations/${this.orgSlug}/users/${userId}/role`, {
                method: 'PATCH',
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ role: newRole })
            })
            .then(() => {
                this.loadUsers();
                alert('Role updated successfully!');
            });
        },

        openPasswordModal(userId) {
            this.passwordData.user_id = userId;
            this.passwordData.password = '';
            this.showPassword = true;
        },

        updatePassword() {
            fetch(`/api/organizations/${this.orgSlug}/users/${this.passwordData.user_id}/password`, {
                method: 'PATCH',
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ password: this.passwordData.password })
            })
            .then(() => {
                this.showPassword = false;
                alert('Password updated successfully!');
            });
        },

        deleteUser(id) {
            if (!confirm('Remove this user?')) return;

            fetch(`/api/organizations/${this.orgSlug}/users/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + this.token }
            })
            .then(() => {
                this.loadUsers();
                alert('User removed successfully!');
            });
        }
    }"
>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Manage Users</h1>
            <p class="text-sm text-gray-500 mt-1">Add or remove users for this organization</p>
        </div>

        <button @click="showCreate = true"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Add User
        </button>
    </div>

    <!-- Create Modal -->
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Add User</h2>
            <form @submit.prevent="createUser">

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Name</label>
                    <input type="text" x-model="newUser.name" required class="w-full px-3 py-2 border rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" x-model="newUser.email" required class="w-full px-3 py-2 border rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" x-model="newUser.password" required minlength="8"
                           class="w-full px-3 py-2 border rounded">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Role</label>
                    <select x-model="newUser.role" class="w-full px-3 py-2 border rounded">
                        <option value="admin">Admin (Full Access)</option>
                        <option value="user">User (Read-only)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add User
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

    <!-- Loading -->
    <template x-if="loading">
        <div class="text-center py-12"><p>Loading...</p></div>
    </template>

    <!-- Table -->
    <template x-if="!loading">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td class="px-6 py-4 font-medium" x-text="user.name"></td>
                            <td class="px-6 py-4 text-sm" x-text="user.email"></td>

                            <td class="px-6 py-4 text-sm">
                                <select :value="user.role" @change="updateRole(user.id, $event.target.value)"
                                    class="px-2 py-1 border rounded text-xs">
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </td>

                            <td class="px-6 py-4 text-sm space-x-2">
                                <button @click="openPasswordModal(user.id)"
                                    class="text-blue-600 hover:text-blue-800">Change Password</button>

                                <button @click="deleteUser(user.id)"
                                    class="text-red-600 hover:text-red-800">Remove</button>
                            </td>
                        </tr>
                    </template>
                </tbody>

            </table>
        </div>
    </template>

</div>
@endsection
