<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Saiffil Fariz',
            'email' => 'saiffil@gmail.com',
            'password' => bcrypt('111111'),
            'is_admin' => true,
        ]);

        // Seed books and categories
        $this->call([
            BookSeeder::class,
        ]);
    }
}
