<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileOrders extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.profile-orders', [
            'orders' => Order::where('user_id', Auth::id())->with('items')->latest()->paginate(10),
        ])->layout('layouts.app');
    }
}
