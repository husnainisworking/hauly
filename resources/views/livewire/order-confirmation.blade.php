<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-10">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Order Confirmed!</h1>
        <p class="mt-2 text-gray-600">Thank you for your purchase.</p>
        <p class="mt-1 font-mono text-indigo-600 font-semibold text-lg">{{ $order->order_number }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="font-bold text-gray-900 mb-4">Order Details</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex items-center gap-4">
                    @if($item->product)
                        <img src="{{ $item->product->primary_image }}" alt="{{ $item->product_name }}" class="w-14 h-14 object-cover rounded-xl flex-none" />
                    @endif
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-500">Qty: {{ $item->quantity }} × ${{ number_format($item->product_price, 2) }}</p>
                    </div>
                    <span class="font-semibold">${{ number_format($item->total, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="p-6 border-b bg-gray-50">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-gray-600"><span>Tax</span><span>${{ number_format($order->tax, 2) }}</span></div>
                <div class="flex justify-between text-gray-600"><span>Shipping</span><span class="text-green-600">Free</span></div>
                <div class="flex justify-between font-bold text-gray-900 text-base border-t pt-2"><span>Total</span><span>${{ number_format($order->total, 2) }}</span></div>
            </div>
        </div>
        <div class="p-6">
            <h3 class="font-semibold text-gray-900 mb-2">Shipping To</h3>
            <p class="text-gray-600 text-sm">
                {{ $order->shipping_address['first_name'] }} {{ $order->shipping_address['last_name'] }}<br>
                {{ $order->shipping_address['address'] }}<br>
                {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }} {{ $order->shipping_address['zip'] }}<br>
                {{ $order->shipping_address['country'] }}
            </p>
        </div>
    </div>
    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('home') }}" wire:navigate class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors text-center">Continue Shopping</a>
        @auth
            <a href="{{ route('profile.orders') }}" wire:navigate class="px-8 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors text-center">View My Orders</a>
        @endauth
    </div>
</div>
