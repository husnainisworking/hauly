<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@hauly.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Customer user
        User::factory()->create([
            'name'     => 'John Customer',
            'email'    => 'customer@hauly.com',
            'password' => Hash::make('password'),
            'role'     => 'customer',
        ]);

        // Categories
        $categories = [
            ['name' => 'Electronics',   'sort_order' => 1],
            ['name' => 'Clothing',       'sort_order' => 2],
            ['name' => 'Home & Garden',  'sort_order' => 3],
            ['name' => 'Books',          'sort_order' => 4],
            ['name' => 'Sports',         'sort_order' => 5],
            ['name' => 'Beauty',         'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'is_active'  => true,
                'sort_order' => $cat['sort_order'],
            ]);
        }

        // Products
        $products = [
            ['name' => 'Wireless Headphones', 'category' => 'Electronics', 'price' => 79.99,  'compare' => 129.99, 'stock' => 50,  'featured' => true],
            ['name' => 'Smart Watch Pro',      'category' => 'Electronics', 'price' => 199.99, 'compare' => null,   'stock' => 30,  'featured' => true],
            ['name' => 'USB-C Hub 7-in-1',     'category' => 'Electronics', 'price' => 39.99,  'compare' => 59.99,  'stock' => 100, 'featured' => false],
            ['name' => 'Classic Denim Jacket', 'category' => 'Clothing',    'price' => 59.99,  'compare' => 89.99,  'stock' => 25,  'featured' => true],
            ['name' => 'Premium Cotton Tee',   'category' => 'Clothing',    'price' => 24.99,  'compare' => null,   'stock' => 200, 'featured' => false],
            ['name' => 'Yoga Mat Pro',          'category' => 'Sports',      'price' => 34.99,  'compare' => 49.99,  'stock' => 75,  'featured' => true],
            ['name' => 'Bamboo Cutting Board',  'category' => 'Home & Garden','price' => 19.99, 'compare' => null,   'stock' => 60,  'featured' => false],
            ['name' => 'Scented Candle Set',    'category' => 'Home & Garden','price' => 29.99, 'compare' => 39.99,  'stock' => 40,  'featured' => false],
            ['name' => 'JavaScript Mastery',    'category' => 'Books',       'price' => 44.99,  'compare' => null,   'stock' => 150, 'featured' => false],
            ['name' => 'Vitamin C Serum',       'category' => 'Beauty',      'price' => 27.99,  'compare' => 39.99,  'stock' => 80,  'featured' => true],
            ['name' => 'Resistance Band Set',   'category' => 'Sports',      'price' => 22.99,  'compare' => null,   'stock' => 90,  'featured' => false],
            ['name' => 'Leather Wallet',        'category' => 'Clothing',    'price' => 49.99,  'compare' => 69.99,  'stock' => 35,  'featured' => false],
        ];

        foreach ($products as $p) {
            $category = Category::where('name', $p['category'])->first();
            Product::create([
                'category_id'       => $category->id,
                'name'              => $p['name'],
                'slug'              => Str::slug($p['name']),
                'short_description' => 'High-quality ' . strtolower($p['name']) . ' designed for everyday use.',
                'description'       => 'This premium ' . strtolower($p['name']) . ' offers excellent quality and durability. Perfect for personal use or as a gift. Made with the finest materials.',
                'price'             => $p['price'],
                'compare_price'     => $p['compare'],
                'sku'               => strtoupper(Str::random(6)) . '-' . rand(100, 999),
                'stock'             => $p['stock'],
                'is_active'         => true,
                'is_featured'       => $p['featured'],
                'track_stock'       => true,
                'images'            => [
                    'https://placehold.co/600x600/e0e7ff/4f46e5?text=' . urlencode($p['name']),
                ],
            ]);
        }
    }
}
