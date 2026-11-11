<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Pupuk NPK Phonska Plus 15-15-15',
            'price' => 218000,
            'stock' => 10,
        ]);
    }
}
