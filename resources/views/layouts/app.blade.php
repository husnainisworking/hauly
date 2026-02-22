<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Hauly Store') }}{{ isset($title) ? ' — ' . $title : '' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-50 antialiased font-sans" x-data="notifications()" x-init="init()">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">{{ config('app.name') }}</span>
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" wire:navigate class="text-gray-600 hover:text-indigo-600 font-medium transition-colors text-sm">Home</a>
                    <a href="{{ route('shop') }}" wire:navigate class="text-gray-600 hover:text-indigo-600 font-medium transition-colors text-sm">Shop</a>
                </div>

                <div class="flex items-center gap-2">
                    <livewire:navbar-cart />

                    @guest
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors px-3 py-2">Login</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition-colors">Sign up</a>
                    @endguest

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-indigo-600 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-7 h-7 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Admin Dashboard</a>
                                    <div class="border-t my-1"></div>
                                @endif
                                <a href="{{ route('profile.orders') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Orders</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Logout</button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash notifications --}}
    <div x-cloak class="fixed top-4 right-4 z-50 space-y-2" @notify.window="show($event.detail)">
        <template x-for="(notif, i) in notifications" :key="i">
            <div x-show="notif.visible" x-transition
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium min-w-[260px]"
                :class="{
                    'bg-green-600 text-white': notif.type === 'success',
                    'bg-red-600 text-white': notif.type === 'error',
                    'bg-blue-600 text-white': notif.type === 'info',
                }">
                <span x-text="notif.message"></span>
            </div>
        </template>
    </div>

    <main>{{ $slot }}</main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-900">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-gray-500 text-sm">Premium products delivered to your doorstep.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('home') }}" wire:navigate class="hover:text-indigo-600">Home</a></li>
                        <li><a href="{{ route('shop') }}" wire:navigate class="hover:text-indigo-600">Shop</a></li>
                        <li><a href="{{ route('cart') }}" wire:navigate class="hover:text-indigo-600">Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Account</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        @guest
                            <li><a href="{{ route('login') }}" class="hover:text-indigo-600">Login</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-indigo-600">Register</a></li>
                        @else
                            <li><a href="{{ route('profile.orders') }}" wire:navigate class="hover:text-indigo-600">My Orders</a></li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div class="border-t mt-8 pt-6 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Built with the TALL Stack.
            </div>
        </div>
    </footer>

    <script>
    function notifications() {
        return {
            notifications: [],
            show(detail) {
                const notif = { message: detail.message, type: detail.type || 'success', visible: true };
                this.notifications.push(notif);
                setTimeout(() => {
                    notif.visible = false;
                    setTimeout(() => { this.notifications = this.notifications.filter(n => n !== notif); }, 300);
                }, 3500);
            },
            init() {}
        }
    }
    </script>
</body>
</html>
