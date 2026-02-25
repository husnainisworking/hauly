<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRevenue   = Order::where('payment_status', 'paid')->sum('total');
        $todayRevenue   = Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total');
        $totalOrders    = Order::count();
        $pendingOrders  = Order::where('status', 'pending')->count();
        $totalProducts  = Product::count();
        $lowStockCount  = Product::where('track_stock', true)->where('stock', '<=', 5)->count();
        $totalCustomers = User::where('role', 'customer')->count();

        return [
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('All-time paid orders')
                ->color('success'),

            Stat::make("Today's Revenue", '$' . number_format($todayRevenue, 2))
                ->description('Paid orders today')
                ->color('info'),

            Stat::make('Total Orders', $totalOrders)
                ->description("{$pendingOrders} pending")
                ->color('primary'),

            Stat::make('Products', $totalProducts)
                ->description("{$lowStockCount} low stock (≤5)")
                ->color($lowStockCount > 0 ? 'warning' : 'success'),

            Stat::make('Customers', $totalCustomers)
                ->description('Registered customers')
                ->color('gray'),
        ];
    }
}
