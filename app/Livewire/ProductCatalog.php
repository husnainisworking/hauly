<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'newest';

    #[Url]
    public string $minPrice = '';

    #[Url]
    public string $maxPrice = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }
    public function updatedMinPrice(): void { $this->resetPage(); }
    public function updatedMaxPrice(): void { $this->resetPage(); }

    public function addToCart(int $productId): void
    {
        $product = Product::active()->findOrFail($productId);
        if (!$product->isInStock()) {
            $this->dispatch('notify', type: 'error', message: 'Product is out of stock.');
            return;
        }
        app(CartService::class)->add($product);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'success', message: '"' . $product->name . '" added to cart!');
    }

    public function render()
    {
        $query = Product::active()->with('category');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $this->category));
        }

        if ($this->minPrice !== '') {
            $query->where('price', '>=', (float) $this->minPrice);
        }

        if ($this->maxPrice !== '') {
            $query->where('price', '<=', (float) $this->maxPrice);
        }

        $query->orderBy(match ($this->sort) {
            'price_asc'  => 'price',
            'price_desc' => 'price',
            'name'       => 'name',
            default      => 'created_at',
        }, match ($this->sort) {
            'price_asc'  => 'asc',
            'price_desc' => 'desc',
            'name'       => 'asc',
            default      => 'desc',
        });

        return view('livewire.product-catalog', [
            'products'   => $query->paginate(12),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ])->layout('layouts.app');
    }
}
