<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class NavbarCart extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = app(CartService::class)->getCount();
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->count = app(CartService::class)->getCount();
    }

    public function render()
    {
        return view('livewire.navbar-cart');
    }
}
