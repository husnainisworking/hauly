<?php
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

new class extends Component {
    public function render()
    {
        return [
            'featuredProducts' => Product::active()->featured()->with('category')->limit(8)->get(),
            'categories'       => Category::where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
        ];
    }
};
?>

<div>
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
            <h1 class="text-5xl font-bold mb-5 leading-tight">Shop the Best Products<br>at the Best Prices</h1>
            <p class="text-xl text-indigo-200 mb-8 max-w-xl mx-auto">Quality products curated for you. Fast shipping, easy returns.</p>
            <a href="{{ route('shop') }}" wire:navigate
                class="inline-block bg-white text-indigo-700 font-bold px-8 py-4 rounded-2xl hover:bg-indigo-50 transition-colors text-lg shadow-lg">
                Browse Shop →
            </a>
        </div>
    </section>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Shop by Category</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('shop', ['category' => $category->slug]) }}" wire:navigate
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-indigo-100 transition-colors">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-indigo-700 transition-colors">{{ $category->name }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured Products --}}
    @if($featuredProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
                <a href="{{ route('shop') }}" wire:navigate class="text-indigo-600 text-sm font-medium hover:underline">View all →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    <a href="{{ route('products.show', $product->slug) }}" wire:navigate
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                        <div class="aspect-square overflow-hidden bg-gray-50">
                            <img src="{{ $product->primary_image }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        </div>
                        <div class="p-4">
                            @if($product->category)
                                <span class="text-xs text-indigo-600 font-medium">{{ $product->category->name }}</span>
                            @endif
                            <h3 class="mt-1 font-semibold text-gray-900 line-clamp-2">{{ $product->name }}</h3>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @if($product->isOnSale())
                                    <span class="text-sm text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
