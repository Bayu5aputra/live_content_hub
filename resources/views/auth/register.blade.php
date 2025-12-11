@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto px-4" x-data="{
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    error: '',
    loading: false,
    register() {
        this.error = '';
        this.loading = true;
        fetch('/api/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: this.name,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.access_token) {
                localStorage.setItem('token', d.access_token);
                window.location.href = '/dashboard';
            } else {
                this.error = d.message || 'Registration failed';
            }
        })
        .catch(() => this.error = 'Network error')
        .finally(() => this.loading = false);
    }
}">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded" x-text="error"></div>
        </template>

        <form @submit.prevent="register">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Nama</label>
                <input type="text" x-model="name" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" x-model="email" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" x-model="password" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" x-model="password_confirmation" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" :disabled="loading"
                class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                <span x-show="!loading">Register</span>
                <span x-show="loading">Loading...</span>
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            Sudah punya akun? <a href="/login" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</div>
@endsection
