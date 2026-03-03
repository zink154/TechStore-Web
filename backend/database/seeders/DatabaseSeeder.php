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
        // Roles (skip if already exist)
        if (Role::count() === 0) {
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'customer']);
        }

        // Admin account
        if (!User::where('email', 'admin@techstore.test')->exists()) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@techstore.test',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('admin');
        }

        // Customer account
        if (!User::where('email', 'user@techstore.test')->exists()) {
            $customer = User::create([
                'name' => 'Customer',
                'email' => 'user@techstore.test',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $customer->assignRole('customer');
        }

        // Categories + Products (skip if already seeded)
        if (Category::count() > 0) {
            return;
        }

        $categories = collect([
            'Laptops', 'Smartphones', 'Tablets', 'Accessories',
            'Audio', 'Gaming',
        ])->map(fn ($name) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));

        $products = [
            ['name' => 'MacBook Pro 16"', 'category' => 'Laptops', 'price' => 2499.99, 'stock' => 10, 'description' => 'Apple M3 Pro chip, 18GB RAM, 512GB SSD. Stunning Liquid Retina XDR display with ProMotion.'],
            ['name' => 'Dell XPS 15', 'category' => 'Laptops', 'price' => 1799.99, 'stock' => 15, 'description' => 'Intel Core i7-13700H, 16GB RAM, 512GB SSD. 15.6" OLED 3.5K InfinityEdge display.'],
            ['name' => 'ThinkPad X1 Carbon', 'category' => 'Laptops', 'price' => 1599.99, 'stock' => 12, 'description' => 'Intel Core i7, 16GB RAM, 512GB SSD. Ultra-light 14" business laptop with legendary keyboard.'],
            ['name' => 'iPhone 16 Pro', 'category' => 'Smartphones', 'price' => 1199.99, 'stock' => 25, 'description' => 'A18 Pro chip, 48MP camera system, titanium design. 6.3" Super Retina XDR with ProMotion.'],
            ['name' => 'Samsung Galaxy S25', 'category' => 'Smartphones', 'price' => 999.99, 'stock' => 20, 'description' => 'Snapdragon 8 Elite, 12GB RAM, 200MP camera. 6.2" Dynamic AMOLED 2X display.'],
            ['name' => 'Google Pixel 9', 'category' => 'Smartphones', 'price' => 899.99, 'stock' => 18, 'description' => 'Google Tensor G4, best-in-class camera with AI features. Pure Android experience.'],
            ['name' => 'iPad Pro 13"', 'category' => 'Tablets', 'price' => 1299.99, 'stock' => 8, 'description' => 'M4 chip, Ultra Retina XDR tandem OLED display. Thinnest Apple product ever.'],
            ['name' => 'Samsung Galaxy Tab S10', 'category' => 'Tablets', 'price' => 849.99, 'stock' => 10, 'description' => 'MediaTek Dimensity 9300+, 12GB RAM. 12.4" Dynamic AMOLED 2X with S Pen included.'],
            ['name' => 'AirPods Pro 3', 'category' => 'Audio', 'price' => 249.99, 'stock' => 50, 'description' => 'Active Noise Cancellation, Adaptive Audio, USB-C. Up to 6 hours listening time.'],
            ['name' => 'Sony WH-1000XM6', 'category' => 'Audio', 'price' => 349.99, 'stock' => 30, 'description' => 'Industry-leading noise cancellation, 30-hour battery. Hi-Res Audio with LDAC.'],
            ['name' => 'MagSafe Charger', 'category' => 'Accessories', 'price' => 39.99, 'stock' => 100, 'description' => 'Perfectly aligned wireless charging for iPhone. Up to 15W fast charging with MagSafe.'],
            ['name' => 'USB-C Hub 7-in-1', 'category' => 'Accessories', 'price' => 59.99, 'stock' => 60, 'description' => 'HDMI 4K@60Hz, USB-A 3.0, SD/microSD, USB-C PD 100W pass-through charging.'],
            ['name' => 'PS5 DualSense Controller', 'category' => 'Gaming', 'price' => 69.99, 'stock' => 40, 'description' => 'Haptic feedback, adaptive triggers. Immersive gaming experience with built-in mic.'],
            ['name' => 'Razer DeathAdder V3', 'category' => 'Gaming', 'price' => 89.99, 'stock' => 35, 'description' => '30K DPI optical sensor, ultra-lightweight 59g. Ergonomic design for competitive gaming.'],
            ['name' => 'Mechanical Keyboard K8', 'category' => 'Gaming', 'price' => 129.99, 'stock' => 25, 'description' => 'Hot-swappable switches, RGB backlight, aluminum frame. Tenkeyless wireless layout.'],
        ];

        foreach ($products as $p) {
            $category = $categories->firstWhere('name', $p['category']);
            Product::create([
                'category_id' => $category->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'description' => $p['description'],
                'price' => $p['price'],
                'stock' => $p['stock'],
                'image_url' => 'https://picsum.photos/seed/' . Str::slug($p['name']) . '/640/480',
                'is_active' => true,
            ]);
        }
    }
}
