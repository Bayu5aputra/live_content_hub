@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto px-4" x-data="{
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    organization_code: '',
    error: '',
    loading: false,
    register() {
        this.error = '';
        this.loading = true;
        
        // Validasi frontend
        if (this.password !== this.password_confirmation) {
            this.error = 'Password dan konfirmasi password tidak sama';
            this.loading = false;
            return;
        }
        
        if (this.password.length < 8) {
            this.error = 'Password minimal 8 karakter';
            this.loading = false;
            return;
        }
        
        if (!this.organization_code) {
            this.error = 'Organization code wajib diisi';
            this.loading = false;
            return;
        }
        
        fetch('/api/register', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: this.name,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation,
                organization_code: this.organization_code
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json().then(data => ({
                status: response.status,
                data: data
            }));
        })
        .then(result => {
            console.log('Result:', result);
            
            if (result.status === 201 && result.data.access_token) {
                localStorage.setItem('token', result.data.access_token);
                window.location.href = '/dashboard';
            } else {
                // Handle validation errors
                if (result.data.errors) {
                    const errors = Object.values(result.data.errors).flat();
                    this.error = errors.join(', ');
                } else {
                    this.error = result.data.message || 'Registration failed';
                }
            }
        })
        .catch(err => {
            console.error('Network error:', err);
            this.error = 'Network error: Tidak dapat terhubung ke server. Pastikan server Laravel sudah berjalan.';
        })
        .finally(() => this.loading = false);
    }
}">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-2 text-center">Register Organization Admin</h2>
        <p class="text-sm text-gray-600 mb-6 text-center">Daftar sebagai admin organisasi dengan kode organisasi</p>

        <template x-if="error">
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm" x-text="error"></div>
        </template>

        <form @submit.prevent="register">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Nama Lengkap</label>
                <input type="text" x-model="name" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Nama lengkap">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" x-model="email" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="email@example.com">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Kode Organisasi</label>
                <input type="text" x-model="organization_code" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 uppercase"
                    placeholder="Masukkan kode organisasi"
                    @input="organization_code = organization_code.toUpperCase()">
                <p class="text-xs text-gray-500 mt-1">Hubungi super admin untuk mendapatkan kode organisasi</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" x-model="password" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Minimal 8 karakter">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" x-model="password_confirmation" required
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Ketik ulang password">
            </div>

            <button type="submit" :disabled="loading"
                class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
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
