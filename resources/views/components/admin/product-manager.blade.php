<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $shortDescription = '';
    public string $price = '';
    public string $comparePrice = '';
    public string $sku = '';
    public int $stock = 0;
    public string $categoryId = '';
    public bool $isActive = true;
    public bool $isFeatured = false;
    public bool $trackStock = true;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedName(): void { if (!$this->editing) $this->slug = Str::slug($this->name); }

    public function openCreate(): void
    {
        $this->reset(['name', 'slug', 'description', 'shortDescription', 'price', 'comparePrice', 'sku', 'stock', 'categoryId', 'editingId']);
        $this->isActive = true;
        $this->isFeatured = false;
        $this->trackStock = true;
        $this->editing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = $product->description ?? '';
        $this->shortDescription = $product->short_description ?? '';
        $this->price = $product->price;
        $this->comparePrice = $product->compare_price ?? '';
        $this->sku = $product->sku ?? '';
        $this->stock = $product->stock;
        $this->categoryId = $product->category_id ?? '';
        $this->isActive = $product->is_active;
        $this->isFeatured = $product->is_featured;
        $this->trackStock = $product->track_stock;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'comparePrice' => 'nullable|numeric|min:0',
            'sku'         => 'nullable|string|max:100',
            'stock'       => 'required|integer|min:0',
            'categoryId'  => 'nullable|exists:categories,id',
        ]);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->slug),
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'price' => $this->price,
            'compare_price' => $this->comparePrice ?: null,
            'sku' => $this->sku ?: null,
            'stock' => $this->stock,
            'category_id' => $this->categoryId ?: null,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
            'track_stock' => $this->trackStock,
        ];

        if ($this->editing) {
            Product::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Product updated.');
        } else {
            Product::create($data);
            $this->dispatch('notify', type: 'success', message: 'Product created.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'info', message: 'Product deleted.');
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
    }

    public function render()
    {
        $query = Product::with('category');
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
        }
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        return [
            'products'   => $query->latest()->paginate(15),
            'categories' => Category::orderBy('name')->get(),
        ];
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
        <button wire:click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
            + Add Product
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search products..."
            class="flex-1 max-w-sm px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500" />
        <select wire:model.live="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-right">Price</th>
                        <th class="px-5 py-3 text-right">Stock</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="product-{{ $product->id }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->primary_image }}" alt="{{ $product->name }}"
                                        class="w-10 h-10 object-cover rounded-lg flex-none" />
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                        @if($product->sku)
                                            <p class="text-xs text-gray-400 font-mono">{{ $product->sku }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <p class="font-semibold">${{ number_format($product->price, 2) }}</p>
                                @if($product->isOnSale())
                                    <p class="text-xs text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($product->track_stock)
                                    <span class="{{ $product->stock <= 5 ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $product->stock }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">Unlimited</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <button wire:click="toggleActive({{ $product->id }})"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $product->is_active ? 'Active' : 'Draft' }}
                                </button>
                                @if($product->is_featured)
                                    <span class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Featured</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openEdit({{ $product->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                                    <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $products->links() }}</div>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b flex items-center justify-between">
                    <h2 class="text-lg font-bold">{{ $editing ? 'Edit Product' : 'Add Product' }}</h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input wire:model.live="name" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                            <input wire:model="slug" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                            <input wire:model="price" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                            @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price</label>
                            <input wire:model="comparePrice" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input wire:model="sku" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select wire:model="categoryId" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="">No category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                            <input wire:model="shortDescription" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input wire:model="trackStock" type="checkbox" id="trackStock" class="rounded" />
                                <label for="trackStock" class="text-sm font-medium text-gray-700">Track Stock</label>
                            </div>
                            @if($trackStock)
                                <input wire:model="stock" type="number" min="0" placeholder="Stock quantity"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 justify-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="isActive" type="checkbox" class="rounded" />
                                <span class="text-sm font-medium text-gray-700">Active (visible in store)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="isFeatured" type="checkbox" class="rounded" />
                                <span class="text-sm font-medium text-gray-700">Featured</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t flex gap-3 justify-end">
                    <button wire:click="$set('showModal', false)" class="px-5 py-2 border border-gray-300 rounded-xl font-medium hover:bg-gray-50">Cancel</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700">
                        <span wire:loading.remove>{{ $editing ? 'Update' : 'Create' }}</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>