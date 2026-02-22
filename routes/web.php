<?php

use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\OrderManager;
use App\Livewire\Admin\ProductManager;
use App\Livewire\Checkout;
use App\Livewire\HomePage;
use App\Livewire\OrderConfirmation;
use App\Livewire\ProductCatalog;
use App\Livewire\ProductDetail;
use App\Livewire\ProfileOrders;
use App\Livewire\ShoppingCart;
use Illuminate\Support\Facades\Route;

// ─── Public store routes ──────────────────────────────────────────────────────
Route::get('/', HomePage::class)->name('home');
Route::get('/shop', ProductCatalog::class)->name('shop');
Route::get('/products/{product:slug}', ProductDetail::class)->name('products.show');
Route::get('/cart', ShoppingCart::class)->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout');
Route::get('/orders/confirmation/{orderNumber}', OrderConfirmation::class)->name('orders.confirmation');

// ─── Authenticated user routes ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile/orders', ProfileOrders::class)->name('profile.orders');
});

// ─── Admin routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/products', ProductManager::class)->name('products');
    Route::get('/categories', CategoryManager::class)->name('categories');
    Route::get('/orders', OrderManager::class)->name('orders');
});

// ─── Auth scaffolding ─────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
