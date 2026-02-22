<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;
    public int $quantity = 1;
    public int $activeImageIndex = 0;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function incrementQty(): void
    {
        $max = $this->product->track_stock ? $this->product->stock : 99;
        if ($this->quantity < $max) $this->quantity++;
    }

    public function decrementQty(): void
    {
        if ($this->quantity > 1) $this->quantity--;
    }

    public function addToCart(): void
    {
        if (!$this->product->isInStock()) {
            $this->dispatch('notify', type: 'error', message: 'Product is out of stock.');
            return;
        }
        app(CartService::class)->add($this->product, $this->quantity);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'success', message: '"' . $this->product->name . '" added to cart!');
    }

    public function render()
    {
        $relatedProducts = Product::active()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->limit(4)->get();

        return view('livewire.product-detail', ['relatedProducts' => $relatedProducts])
            ->layout('layouts.app');
    }
}
