<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Digital Content Hub')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700"
         x-data="{ 
             token: localStorage.getItem('token'), 
             user: null,
             showMobileMenu: false,
             
             init() {
                 if (this.token) {
                     fetch('/api/user', {
                         headers: { 'Authorization': 'Bearer ' + this.token }
                     })
                     .then(r => r.json())
                     .then(d => this.user = d)
                     .catch(() => { 
                         localStorage.removeItem('token'); 
                         this.token = null; 
                     });
                 }
             },
             
             logout() {
                 fetch('/api/logout', {
                     method: 'POST',
                     headers: { 'Authorization': 'Bearer ' + this.token }
                 }).finally(() => {
                     localStorage.removeItem('token');
                     window.location.href = '/login';
                 });
             }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <span class="text-xl font-bold text-gray-800 dark:text-white">Digital Content Hub</span>
                    </a>

                    <!-- Desktop Navigation - Super Admin -->
                    <template x-if="token && user?.is_super_admin">
                        <div class="hidden md:flex items-center ml-10 space-x-4">
                            <a href="/dashboard"
                               class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                               :class="window.location.pathname === '/dashboard' ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : ''">
                                Dashboard
                            </a>
                            <a href="/super-admin/organizations"
                               class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                               :class="window.location.pathname.includes('/super-admin/organizations') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : ''">
                                Organizations
                            </a>
                            <a href="/super-admin/admins"
                               class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                               :class="window.location.pathname.includes('/super-admin/admins') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : ''">
                                Super Admins
                            </a>
                            <a href="/super-admin/profile"
                               class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                               :class="window.location.pathname === '/super-admin/profile' ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : ''">
                                Profile
                            </a>
                        </div>
                    </template>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-4">
                    <template x-if="!token">
                        <div class="flex gap-2">
                            <a href="/login" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Login</a>
                            <a href="/register" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Register</a>
                        </div>
                    </template>

                    <template x-if="token && user">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300 hidden md:block" x-text="user?.name"></span>
                            <button @click="logout()"
                                    class="px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">
                                Logout
                            </button>
                        </div>
                    </template>

                    <!-- Mobile menu button -->
                    <template x-if="token && user?.is_super_admin">
                        <button @click="showMobileMenu = !showMobileMenu"
                                class="md:hidden p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation - Super Admin -->
        <template x-if="token && user?.is_super_admin">
            <div x-show="showMobileMenu"
                 x-transition
                 class="md:hidden border-t border-gray-200 dark:border-gray-700">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="/dashboard" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                        Dashboard
                    </a>
                    <a href="/super-admin/organizations" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                        Organizations
                    </a>
                    <a href="/super-admin/admins" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                        Super Admins
                    </a>
                    <a href="/super-admin/profile" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                        Profile
                    </a>
                </div>
            </div>
        </template>
    </nav>

    <!-- Content -->
    <main class="py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
