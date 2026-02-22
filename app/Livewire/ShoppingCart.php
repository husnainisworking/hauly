<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class ShoppingCart extends Component
{
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
        return view('livewire.shopping-cart', [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ])->layout('layouts.app');
    }
}
