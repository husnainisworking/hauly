<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
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
        if ($this->quantity < $max) {
            $this->quantity++;
        }
    }

    public function decrementQty(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
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
            ->limit(4)
            ->get();

        return ['relatedProducts' => $relatedProducts];
    }
};
?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-indigo-600">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop') }}" wire:navigate class="hover:text-indigo-600">Shop</a>
            @if($product->category)
                <span class="mx-2">/</span>
                <a href="{{ route('shop', ['category' => $product->category->slug]) }}" wire:navigate class="hover:text-indigo-600">{{ $product->category->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Images --}}
            <div>
                <div class="aspect-square rounded-2xl overflow-hidden bg-gray-50 mb-4">
                    <img src="{{ ($product->images ?? [])[$activeImageIndex] ?? $product->primary_image }}"
                         alt="{{ $product->name }}"
                         class="w-full h-full object-cover" />
                </div>
                @if(count($product->images ?? []) > 1)
                    <div class="flex gap-3 overflow-x-auto pb-2">
                        @foreach($product->images as $i => $image)
                            <button wire:click="$set('activeImageIndex', {{ $i }})"
                                class="flex-none w-20 h-20 rounded-xl overflow-hidden border-2 transition-colors
                                    {{ $activeImageIndex === $i ? 'border-indigo-500' : 'border-transparent' }}">
                                <img src="{{ $image }}" alt="" class="w-full h-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                @if($product->category)
                    <span class="text-sm text-indigo-600 font-medium uppercase tracking-wide">{{ $product->category->name }}</span>
                @endif
                <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                    @if($product->isOnSale())
                        <span class="text-xl text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                        <span class="bg-red-100 text-red-600 text-sm font-bold px-2 py-1 rounded-full">
                            Save {{ $product->discount_percent }}%
                        </span>
                    @endif
                </div>

                @if($product->short_description)
                    <p class="mt-4 text-gray-600 text-lg leading-relaxed">{{ $product->short_description }}</p>
                @endif

                @if($product->sku)
                    <p class="mt-3 text-sm text-gray-400">SKU: {{ $product->sku }}</p>
                @endif

                {{-- Stock status --}}
                <div class="mt-4">
                    @if($product->isInStock())
                        <span class="inline-flex items-center gap-1.5 text-green-600 text-sm font-medium">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            In Stock{{ $product->track_stock ? " ({$product->stock} available)" : '' }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-red-600 text-sm font-medium">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            Out of Stock
                        </span>
                    @endif
                </div>

                {{-- Quantity + Add to Cart --}}
                @if($product->isInStock())
                    <div class="mt-6 flex items-center gap-4">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                            <button wire:click="decrementQty" class="px-4 py-3 hover:bg-gray-50 transition-colors text-gray-600 font-bold">-</button>
                            <span class="px-5 py-3 text-center font-semibold min-w-[3rem]">{{ $quantity }}</span>
                            <button wire:click="incrementQty" class="px-4 py-3 hover:bg-gray-50 transition-colors text-gray-600 font-bold">+</button>
                        </div>

                        <button wire:click="addToCart"
                            wire:loading.attr="disabled"
                            class="flex-1 py-3 px-6 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 active:scale-95 transition-all">
                            <span wire:loading.remove>Add to Cart</span>
                            <span wire:loading>Adding...</span>
                        </button>
                    </div>
                @else
                    <button disabled class="mt-6 w-full py-3 px-6 bg-gray-200 text-gray-400 font-semibold rounded-xl cursor-not-allowed">
                        Out of Stock
                    </button>
                @endif

                {{-- Description --}}
                @if($product->description)
                    <div class="mt-8 border-t pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Description</h2>
                        <div class="prose prose-gray max-w-none text-gray-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('products.show', $related->slug) }}" wire:navigate
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                            <div class="aspect-square overflow-hidden bg-gray-50">
                                <img src="{{ $related->primary_image }}" alt="{{ $related->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 line-clamp-1">{{ $related->name }}</h3>
                                <p class="mt-1 font-bold text-indigo-600">${{ number_format($related->price, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
