<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-50 antialiased" x-data="notifications()" x-init="init()">

    <div class="flex h-full min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 flex-none flex flex-col">
            <div class="p-5 border-b border-gray-800">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ config('app.name') }}</p>
                        <p class="text-gray-400 text-xs">Admin Panel</p>
                    </div>
                </a>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                @php
                    $navItems = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'admin.products', 'label' => 'Products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                        ['route' => 'admin.categories', 'label' => 'Categories', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        ['route' => 'admin.orders', 'label' => 'Orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                            {{ request()->routeIs($item['route']) ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="p-4 border-t border-gray-800">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    View Store
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-gray-400 hover:text-red-400 transition-colors text-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-800">Admin Panel</h1>
                <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
            </header>
            <main class="flex-1 overflow-auto p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Flash notification --}}
    <div x-cloak class="fixed top-4 right-4 z-50 space-y-2" @notify.window="show($event.detail)">
        <template x-for="(notif, i) in notifications" :key="i">
            <div x-show="notif.visible" x-transition
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium min-w-[250px]"
                :class="{
                    'bg-green-600 text-white': notif.type === 'success',
                    'bg-red-600 text-white': notif.type === 'error',
                    'bg-blue-600 text-white': notif.type === 'info',
                }">
                <span x-text="notif.message"></span>
            </div>
        </template>
    </div>

    <script>
    function notifications() {
        return {
            notifications: [],
            show(detail) {
                const notif = { message: detail.message, type: detail.type || 'success', visible: true };
                this.notifications.push(notif);
                setTimeout(() => { notif.visible = false; setTimeout(() => { this.notifications = this.notifications.filter(n => n !== notif); }, 300); }, 3500);
            },
            init() {}
        }
    }
    </script>
</body>
</html>
