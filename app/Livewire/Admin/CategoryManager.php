<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryManager extends Component
{
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public bool $isActive = true;
    public int $sortOrder = 0;

    public function updatedName(): void
    {
        if (!$this->editing) $this->slug = Str::slug($this->name);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'slug', 'description', 'editingId']);
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->editing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->isActive = $category->is_active;
        $this->sortOrder = $category->sort_order;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255',
            'sortOrder' => 'integer|min:0',
        ]);

        $data = [
            'name'        => $this->name,
            'slug'        => Str::slug($this->slug),
            'description' => $this->description,
            'is_active'   => $this->isActive,
            'sort_order'  => $this->sortOrder,
        ];

        if ($this->editing) {
            Category::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Category updated.');
        } else {
            Category::create($data);
            $this->dispatch('notify', type: 'success', message: 'Category created.');
        }
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Category::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'info', message: 'Category deleted.');
    }

    public function render()
    {
        return view('livewire.admin.category-manager', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
        ])->layout('layouts.admin');
    }
}
