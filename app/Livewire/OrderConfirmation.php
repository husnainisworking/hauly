<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class OrderConfirmation extends Component
{
    public Order $order;

    public function mount(string $orderNumber): void
    {
        $this->order = Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.order-confirmation')->layout('layouts.app');
    }
}
