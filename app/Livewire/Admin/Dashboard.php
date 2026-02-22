<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalRevenue'   => Order::where('payment_status', 'paid')->sum('total'),
            'todayRevenue'   => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total'),
            'totalOrders'    => Order::count(),
            'pendingOrders'  => Order::where('status', 'pending')->count(),
            'totalProducts'  => Product::count(),
            'lowStockCount'  => Product::where('track_stock', true)->where('stock', '<=', 5)->count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'recentOrders'   => Order::with('user')->latest()->limit(8)->get(),
        ])->layout('layouts.admin');
    }
}
