<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
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

    public function updatedName(): void
    {
        if (!$this->editing) $this->slug = Str::slug($this->name);
    }

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
        $this->price = (string) $product->price;
        $this->comparePrice = (string) ($product->compare_price ?? '');
        $this->sku = $product->sku ?? '';
        $this->stock = $product->stock;
        $this->categoryId = (string) ($product->category_id ?? '');
        $this->isActive = $product->is_active;
        $this->isFeatured = $product->is_featured;
        $this->trackStock = $product->track_stock;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'slug'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'comparePrice' => 'nullable|numeric|min:0',
            'sku'          => 'nullable|string|max:100',
            'stock'        => 'required|integer|min:0',
            'categoryId'   => 'nullable|exists:categories,id',
        ]);

        $data = [
            'name'              => $this->name,
            'slug'              => Str::slug($this->slug),
            'description'       => $this->description,
            'short_description' => $this->shortDescription,
            'price'             => $this->price,
            'compare_price'     => $this->comparePrice ?: null,
            'sku'               => $this->sku ?: null,
            'stock'             => $this->stock,
            'category_id'       => $this->categoryId ?: null,
            'is_active'         => $this->isActive,
            'is_featured'       => $this->isFeatured,
            'track_stock'       => $this->trackStock,
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
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        return view('livewire.admin.product-manager', [
            'products'   => $query->latest()->paginate(15),
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
