<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'user1',
            'password' => 'secret',
            'email' => 'user1@mail.com'
        ]);
        User::factory()->create([
            'name' => 'user2',
            'password' => 'secret',
            'email' => 'user2@mail.com'
        ]);
        User::factory()->create([
            'name' => 'user3',
            'password' => 'secret',
            'email' => 'user3@mail.com'
        ]);
    }
}
