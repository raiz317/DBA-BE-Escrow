<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'seller_id' => 1,
                'name' => 'Mobile Legends',
                'slug' => 'mobile-legends',
                'description' => 'Akun Mobile Legends',
                'price' => 10000,
                'image_url' => 'https://i.imgur.com/8Km9tLL.jpeg',
                'stock' => 999,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 1,
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'description' => 'Akun Free Fire',
                'price' => 12000,
                'image_url' => 'https://i.imgur.com/4QyZ4yM.jpeg',
                'stock' => 999,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 1,
                'name' => 'Valorant',
                'slug' => 'valorant',
                'description' => 'Akun Valorant',
                'price' => 15000,
                'image_url' => 'https://i.imgur.com/BcK6Y6L.jpeg',
                'stock' => 999,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}