<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Component;

new class extends Component {
    public function render()
    {
        $today = now()->startOfDay();

        return [
            'totalRevenue'    => Order::where('payment_status', 'paid')->sum('total'),
            'todayRevenue'    => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total'),
            'totalOrders'     => Order::count(),
            'pendingOrders'   => Order::where('status', 'pending')->count(),
            'totalProducts'   => Product::count(),
            'lowStockCount'   => Product::where('track_stock', true)->where('stock', '<=', 5)->count(),
            'totalCustomers'  => User::where('role', 'customer')->count(),
            'recentOrders'    => Order::with('user')->latest()->limit(8)->get(),
        ];
    }
};
?>

<div>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($totalRevenue, 2) }}</p>
                        <p class="text-xs text-green-600 mt-0.5">Today: ${{ number_format($todayRevenue, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalOrders }}</p>
                        <p class="text-xs text-amber-600 mt-0.5">{{ $pendingOrders }} pending</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Products</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalProducts }}</p>
                        @if($lowStockCount > 0)
                            <p class="text-xs text-red-500 mt-0.5">{{ $lowStockCount }} low stock</p>
                        @else
                            <p class="text-xs text-green-600 mt-0.5">All stocked</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Customers</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalCustomers }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b flex items-center justify-between">
                <h2 class="font-bold text-gray-900">Recent Orders</h2>
                <a href="{{ route('admin.orders') }}" wire:navigate class="text-sm text-indigo-600 hover:underline">View all →</a>
            </div>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-mono text-indigo-600 font-medium">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" wire:navigate class="hover:underline">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $order->user?->name ?? ($order->shipping_address['first_name'] . ' ' . $order->shipping_address['last_name']) }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($order->status === 'delivered') bg-green-100 text-green-700
                                        @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                        @else bg-yellow-100 text-yellow-700
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-xs {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold">${{ number_format($order->total, 2) }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>