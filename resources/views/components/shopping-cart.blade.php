<?php

use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    #[On('cart-updated')]
    public function refresh(): void {}

    public function updateQuantity(int $itemId, int $quantity): void
    {
        app(CartService::class)->update($itemId, $quantity);
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        app(CartService::class)->remove($itemId);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'info', message: 'Item removed from cart.');
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $cart = app(CartService::class);
        return [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ];
    }
};
?>

<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        @if($items->isEmpty())
            <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="mt-4 text-xl text-gray-500">Your cart is empty</p>
                <a href="{{ route('shop') }}" wire:navigate
                    class="mt-6 inline-block bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                    Browse Products
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Cart Items --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach($items as $item)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex gap-4" wire:key="cart-item-{{ $item->id }}">
                            <a href="{{ route('products.show', $item->product->slug) }}" wire:navigate class="flex-none">
                                <img src="{{ $item->product->primary_image }}" alt="{{ $item->product->name }}"
                                    class="w-20 h-20 object-cover rounded-xl" />
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('products.show', $item->product->slug) }}" wire:navigate>
                                    <h3 class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2">{{ $item->product->name }}</h3>
                                </a>
                                <p class="text-sm text-gray-500 mt-0.5">${{ number_format($item->product->price, 2) }} each</p>

                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                            class="px-3 py-1.5 hover:bg-gray-50 transition-colors text-gray-600">−</button>
                                        <span class="px-4 py-1.5 font-medium text-sm min-w-[2.5rem] text-center">{{ $item->quantity }}</span>
                                        <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                            class="px-3 py-1.5 hover:bg-gray-50 transition-colors text-gray-600">+</button>
                                    </div>
                                    <span class="font-bold text-gray-900">${{ number_format($item->subtotal, 2) }}</span>
                                </div>
                            </div>
                            <button wire:click="removeItem({{ $item->id }})"
                                class="flex-none text-gray-300 hover:text-red-400 transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach

                    <div class="flex justify-between items-center">
                        <a href="{{ route('shop') }}" wire:navigate class="text-indigo-600 hover:underline text-sm">← Continue Shopping</a>
                        <button wire:click="clearCart" wire:confirm="Clear your entire cart?"
                            class="text-sm text-red-500 hover:text-red-700 transition-colors">
                            Clear Cart
                        </button>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ $items->sum('quantity') }} items)</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span class="text-green-600">Free</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between font-bold text-gray-900 text-base">
                                <span>Total</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout') }}" wire:navigate
                            class="mt-6 block w-full text-center bg-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
