<a href="{{ route('cart') }}" wire:navigate class="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
    </svg>
    @if($count > 0)
        <span class="absolute -top-1 -right-1 w-5 h-5 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>
