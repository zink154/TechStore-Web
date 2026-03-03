<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);

        // Admin account
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@techstore.test',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Customer account
        $customer = User::factory()->create([
            'name' => 'Customer',
            'email' => 'user@techstore.test',
            'password' => bcrypt('password'),
        ]);
        $customer->assignRole('customer');

        // Categories
        $categories = collect([
            'Laptops', 'Smartphones', 'Tablets', 'Accessories',
            'Audio', 'Gaming',
        ])->map(fn ($name) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));

        // Sample products
        $products = [
            ['name' => 'MacBook Pro 16"', 'category' => 'Laptops', 'price' => 2499.99, 'stock' => 10],
            ['name' => 'Dell XPS 15', 'category' => 'Laptops', 'price' => 1799.99, 'stock' => 15],
            ['name' => 'ThinkPad X1 Carbon', 'category' => 'Laptops', 'price' => 1599.99, 'stock' => 12],
            ['name' => 'iPhone 16 Pro', 'category' => 'Smartphones', 'price' => 1199.99, 'stock' => 25],
            ['name' => 'Samsung Galaxy S25', 'category' => 'Smartphones', 'price' => 999.99, 'stock' => 20],
            ['name' => 'Google Pixel 9', 'category' => 'Smartphones', 'price' => 899.99, 'stock' => 18],
            ['name' => 'iPad Pro 13"', 'category' => 'Tablets', 'price' => 1299.99, 'stock' => 8],
            ['name' => 'Samsung Galaxy Tab S10', 'category' => 'Tablets', 'price' => 849.99, 'stock' => 10],
            ['name' => 'AirPods Pro 3', 'category' => 'Audio', 'price' => 249.99, 'stock' => 50],
            ['name' => 'Sony WH-1000XM6', 'category' => 'Audio', 'price' => 349.99, 'stock' => 30],
            ['name' => 'MagSafe Charger', 'category' => 'Accessories', 'price' => 39.99, 'stock' => 100],
            ['name' => 'USB-C Hub 7-in-1', 'category' => 'Accessories', 'price' => 59.99, 'stock' => 60],
            ['name' => 'PS5 DualSense Controller', 'category' => 'Gaming', 'price' => 69.99, 'stock' => 40],
            ['name' => 'Razer DeathAdder V3', 'category' => 'Gaming', 'price' => 89.99, 'stock' => 35],
            ['name' => 'Mechanical Keyboard K8', 'category' => 'Gaming', 'price' => 129.99, 'stock' => 25],
        ];

        foreach ($products as $p) {
            $category = $categories->firstWhere('name', $p['category']);
            Product::create([
                'category_id' => $category->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'description' => "High-quality {$p['name']} — perfect for tech enthusiasts.",
                'price' => $p['price'],
                'stock' => $p['stock'],
                'is_active' => true,
            ]);
        }
    }
}
