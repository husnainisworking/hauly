<?php

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showDetailModal = false;
    public ?Order $selectedOrder = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function viewOrder(int $id): void
    {
        $this->selectedOrder = Order::with('items.product', 'user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function updateStatus(int $id, string $status): void
    {
        $order = Order::findOrFail($id);
        $extra = [];
        if ($status === 'shipped') $extra['shipped_at'] = now();
        if ($status === 'delivered') $extra['delivered_at'] = now();
        $order->update(array_merge(['status' => $status], $extra));

        if ($this->selectedOrder && $this->selectedOrder->id === $id) {
            $this->selectedOrder->refresh();
        }

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

        return ['orders' => $query->paginate(15)];
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
    </div>

    {{-- Filters --}}
    <div class="flex gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search order number..."
            class="flex-1 max-w-sm px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500" />
        <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Order #</th>
                        <th class="px-5 py-3 text-left">Customer</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Payment</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="order-{{ $order->id }}">
                            <td class="px-5 py-3 font-mono text-indigo-600 font-medium text-xs">{{ $order->order_number }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $order->user?->name ?? $order->shipping_address['first_name'] . ' ' . $order->shipping_address['last_name'] }}</td>
                            <td class="px-5 py-3">
                                <select wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500
                                        @if($order->status === 'delivered') text-green-700 bg-green-50
                                        @elseif($order->status === 'shipped') text-purple-700 bg-purple-50
                                        @elseif($order->status === 'cancelled') text-red-700 bg-red-50
                                        @elseif($order->status === 'processing') text-blue-700 bg-blue-50
                                        @else text-yellow-700 bg-yellow-50 @endif">
                                    @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                                        <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-3 text-xs {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">
                                {{ ucfirst($order->payment_status) }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold">${{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3 text-center">
                                <button wire:click="viewOrder({{ $order->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $orders->links() }}</div>
    </div>

    {{-- Order Detail Modal --}}
    @if($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showDetailModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Order {{ $selectedOrder->order_number }}</h2>
                        <p class="text-sm text-gray-500">{{ $selectedOrder->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs uppercase font-medium mb-1">Customer</p>
                            <p class="font-medium">{{ $selectedOrder->user?->name ?? $selectedOrder->shipping_address['first_name'] . ' ' . $selectedOrder->shipping_address['last_name'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase font-medium mb-1">Status</p>
                            <p class="font-semibold capitalize">{{ $selectedOrder->status }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase font-medium mb-1">Shipping To</p>
                            <p>{{ $selectedOrder->shipping_address['address'] }}, {{ $selectedOrder->shipping_address['city'] }}, {{ $selectedOrder->shipping_address['state'] }} {{ $selectedOrder->shipping_address['zip'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase font-medium mb-1">Payment</p>
                            <p class="{{ $selectedOrder->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }} font-medium">{{ ucfirst($selectedOrder->payment_status) }}</p>
                        </div>
                    </div>

                    <div class="border rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-4 py-2 text-left text-xs text-gray-500">Item</th>
                                <th class="px-4 py-2 text-right text-xs text-gray-500">Qty</th>
                                <th class="px-4 py-2 text-right text-xs text-gray-500">Price</th>
                                <th class="px-4 py-2 text-right text-xs text-gray-500">Total</th>
                            </tr></thead>
                            <tbody class="divide-y">
                                @foreach($selectedOrder->items as $item)
                                    <tr><td class="px-4 py-2.5">{{ $item->product_name }}</td>
                                        <td class="px-4 py-2.5 text-right">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2.5 text-right">${{ number_format($item->product_price, 2) }}</td>
                                        <td class="px-4 py-2.5 text-right font-medium">${{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 text-sm">
                                <tr><td colspan="3" class="px-4 py-2 text-right text-gray-500">Subtotal</td><td class="px-4 py-2 text-right">${{ number_format($selectedOrder->subtotal, 2) }}</td></tr>
                                <tr><td colspan="3" class="px-4 py-2 text-right text-gray-500">Tax</td><td class="px-4 py-2 text-right">${{ number_format($selectedOrder->tax, 2) }}</td></tr>
                                <tr><td colspan="3" class="px-4 py-2 text-right font-bold">Total</td><td class="px-4 py-2 text-right font-bold">${{ number_format($selectedOrder->total, 2) }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>