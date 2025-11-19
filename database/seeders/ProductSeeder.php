<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_US');

        for ($i = 0; $i < 50; $i++) {
            Product::create([
                'name' => ucfirst(fake()->word()) . " " . fake()->randomElement(['Starter', 'Basic', 'Pro']) . " Plan",
                'price' => (fake()->randomFloat(0, 1000, 100000)), // random between MYR 10.00 to 1000.00
                'currency' => 'MYR'
            ]);
        }
    }
}
