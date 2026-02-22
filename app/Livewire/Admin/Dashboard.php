<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Concurrency;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        [
            $totalRevenue, 
            $todayRevenue,
            $totalOrders,
            $pendingOrders, 
            $totalProducts, 
            $lowStockCount,
            $totalCustomers,
            $recentOrders,
        ] = Concurrency::run([
            fn() => Order::where('payment_status', 'paid')->sum('total'),
            fn() => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total'),
            fn() => Order::count(),
            fn() => Order::where('status', 'pending')->count(),
            fn() => Product::count(),
            fn() => Product::where('track_stock', true)->where('stock', '<=', 5)->count(),
            fn() => User::where('role', 'customer')->count(),
            fn() => Order::with('user')->latest()->limit(8)->get(),
        ]);



        return view('livewire.admin.dashboard', compact(
        'totalRevenue', 'todayRevenue','totalOrders','pendingOrders', 
        'totalProducts', 'lowStockCount','totalCustomers','recentOrders'
        ))->layout('layouts.admin');
    }
}
