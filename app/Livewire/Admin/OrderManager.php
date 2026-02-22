<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showDetailModal = false;
    public ?int $selectedOrderId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function viewOrder(int $id): void
    {
        $this->selectedOrderId = $id;
        $this->showDetailModal = true;
    }

    public function updateStatus(int $id, string $status): void
    {
        $order = Order::findOrFail($id);
        $extra = [];
        if ($status === 'shipped') $extra['shipped_at'] = now();
        if ($status === 'delivered') $extra['delivered_at'] = now();
        $order->update(array_merge(['status' => $status], $extra));
        $this->dispatch('notify', type: 'success', message: 'Order status updated.');
    }

    public function render()
    {
        $query = Order::with('user')->latest();
        if ($this->search) {
            $query->where('order_number', 'like', '%' . $this->search . '%');
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $selectedOrder = $this->selectedOrderId
            ? Order::with('items.product', 'user')->find($this->selectedOrderId)
            : null;

        return view('livewire.admin.order-manager', [
            'orders'        => $query->paginate(15),
            'selectedOrder' => $selectedOrder,
        ])->layout('layouts.admin');
    }
}
