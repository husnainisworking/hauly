<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Orders</h1>
    @if($orders->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <p class="text-xl text-gray-500">You haven't placed any orders yet.</p>
            <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-block text-indigo-600 hover:underline">Start shopping</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-mono font-semibold text-indigo-600">{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($order->status === 'delivered') bg-green-100 text-green-700
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                        <p class="text-base font-bold text-gray-900 mt-1">${{ number_format($order->total, 2) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    @foreach($order->items->take(3) as $item)
                        <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">{{ $item->product_name }} ×{{ $item->quantity }}</span>
                    @endforeach
                    @if($order->items->count() > 3)
                        <span class="text-sm text-gray-400">+{{ $order->items->count() - 3 }} more</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
