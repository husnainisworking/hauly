<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class HomePage extends Component
{
    public function render()
    {
        return view('livewire.home-page', [
            'featuredProducts' => Product::active()->featured()->with('category')->limit(8)->get(),
            'categories'       => Category::where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
        ])->layout('layouts.app');
    }
}
