<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('accounts')->insert([

            [
                'product_id' => 1,
                'username' => 'Akun Sultan ML',
                'rank' => 'Mythic Immortal',
                'price' => 500000,
                'image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e',
                'description' => 'Skin banyak dan emblem full',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'product_id' => 1,
                'username' => 'Akun GG ML',
                'rank' => 'Mythic',
                'price' => 250000,
                'image' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420',
                'description' => 'Hero lengkap',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}