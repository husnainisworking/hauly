<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new class extends Component {
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

        return [
            'products'   => $query->paginate(12),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }
};
?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Filters & Search --}}
        <div class="flex flex-col lg:flex-row gap-4 mb-8">
            <div class="flex-1 relative">
                <input wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Search products..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white" />
                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select wire:model.live="category" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="sort" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="newest">Newest First</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="name">Name A-Z</option>
            </select>

            <div class="flex gap-2 items-center">
                <input wire:model.live.debounce.300ms="minPrice" type="number" placeholder="Min $" min="0"
                    class="w-24 px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white" />
                <span class="text-gray-400">—</span>
                <input wire:model.live.debounce.300ms="maxPrice" type="number" placeholder="Max $" min="0"
                    class="w-24 px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white" />
            </div>
        </div>

        {{-- Results count --}}
        <p class="text-sm text-gray-500 mb-4">{{ $products->total() }} products found</p>

        {{-- Product Grid --}}
        @if($products->isEmpty())
            <div class="text-center py-20">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-4 text-gray-500 text-lg">No products found.</p>
                <button wire:click="$set('search', ''); $set('category', '')" class="mt-3 text-indigo-600 hover:underline text-sm">Clear filters</button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                        <a href="{{ route('products.show', $product->slug) }}" wire:navigate>
                            <div class="aspect-square overflow-hidden bg-gray-50">
                                <img src="{{ $product->primary_image }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            </div>
                        </a>

                        <div class="p-4">
                            @if($product->category)
                                <span class="text-xs text-indigo-600 font-medium uppercase tracking-wide">{{ $product->category->name }}</span>
                            @endif

                            <a href="{{ route('products.show', $product->slug) }}" wire:navigate>
                                <h3 class="mt-1 font-semibold text-gray-900 line-clamp-2 hover:text-indigo-600 transition-colors">{{ $product->name }}</h3>
                            </a>

                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @if($product->isOnSale())
                                    <span class="text-sm text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                                    <span class="text-xs bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded-full">-{{ $product->discount_percent }}%</span>
                                @endif
                            </div>

                            @if(!$product->isInStock())
                                <p class="mt-1 text-xs text-red-500 font-medium">Out of Stock</p>
                            @elseif($product->track_stock && $product->stock <= 5)
                                <p class="mt-1 text-xs text-amber-500 font-medium">Only {{ $product->stock }} left</p>
                            @endif

                            <button
                                wire:click="addToCart({{ $product->id }})"
                                wire:loading.attr="disabled"
                                wire:target="addToCart({{ $product->id }})"
                                @disabled(!$product->isInStock())
                                class="mt-3 w-full py-2 px-4 rounded-xl text-sm font-semibold transition-all
                                    {{ $product->isInStock()
                                        ? 'bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                                <span wire:loading.remove wire:target="addToCart({{ $product->id }})">Add to Cart</span>
                                <span wire:loading wire:target="addToCart({{ $product->id }})">Adding...</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
