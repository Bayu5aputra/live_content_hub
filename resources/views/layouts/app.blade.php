<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b" x-data="{ token: localStorage.getItem('token'), user: null }" x-init="
        if (token) {
            fetch('/api/user', {
                headers: { 'Authorization': 'Bearer ' + token }
            })
            .then(r => r.json())
            .then(d => user = d)
            .catch(() => { localStorage.removeItem('token'); token = null; });
        }
    ">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-800">{{ config('app.name') }}</a>
                </div>
                <div class="flex items-center gap-4">
                    <template x-if="!token">
                        <div class="flex gap-2">
                            <a href="/login" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Login</a>
                            <a href="/register" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Register</a>
                        </div>
                    </template>
                    <template x-if="token && user">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-700" x-text="user?.name"></span>
                            <button @click="
                                fetch('/api/logout', {
                                    method: 'POST',
                                    headers: { 'Authorization': 'Bearer ' + token }
                                }).finally(() => {
                                    localStorage.removeItem('token');
                                    window.location.href = '/login';
                                });
                            " class="px-4 py-2 text-sm text-red-600 hover:text-red-700">Logout</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
