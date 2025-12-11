@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto px-4" x-data="{
    email: '',
    password: '',
    error: '',
    loading: false,
    login() {
        this.error = '';
        this.loading = true;
        fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.email, password: this.password })
        })
        .then(r => r.json())
        .then(d => {
            if (d.access_token) {
                localStorage.setItem('token', d.access_token);
                window.location.href = '/dashboard';
            } else {
                this.error = d.message || 'Login failed';
            }
        })
        .catch(() => this.error = 'Network error')
        .finally(() => this.loading = false);
    }
}">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded" x-text="error"></div>
        </template>

        <form @submit.prevent="login">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" x-model="email" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" x-model="password" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" :disabled="loading"
                class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                <span x-show="!loading">Login</span>
                <span x-show="loading">Loading...</span>
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            Belum punya akun? <a href="/register" class="text-blue-600 hover:underline">Register</a>
        </p>
    </div>
</div>
@endsection
