<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Books category
        $bookCategory = Category::firstOrCreate([
            'name' => 'Books'
        ]);

        // Create 20 book products
        $books = [
            [
                'name' => 'The Great Gatsby',
                'price' => 2999, // RM 29.99
            ],
            [
                'name' => 'To Kill a Mockingbird',
                'price' => 3499, // RM 34.99
            ],
            [
                'name' => '1984',
                'price' => 2799, // RM 27.99
            ],
            [
                'name' => 'Pride and Prejudice',
                'price' => 3199, // RM 31.99
            ],
            [
                'name' => 'The Catcher in the Rye',
                'price' => 2899, // RM 28.99
            ],
            [
                'name' => 'Lord of the Flies',
                'price' => 2699, // RM 26.99
            ],
            [
                'name' => 'The Lord of the Rings',
                'price' => 5999, // RM 59.99
            ],
            [
                'name' => 'Harry Potter and the Philosopher\'s Stone',
                'price' => 3999, // RM 39.99
            ],
            [
                'name' => 'The Hobbit',
                'price' => 3299, // RM 32.99
            ],
            [
                'name' => 'Fahrenheit 451',
                'price' => 2599, // RM 25.99
            ],
            [
                'name' => 'Brave New World',
                'price' => 2899, // RM 28.99
            ],
            [
                'name' => 'The Chronicles of Narnia',
                'price' => 4999, // RM 49.99
            ],
            [
                'name' => 'Animal Farm',
                'price' => 2199, // RM 21.99
            ],
            [
                'name' => 'Of Mice and Men',
                'price' => 2399, // RM 23.99
            ],
            [
                'name' => 'The Kite Runner',
                'price' => 3599, // RM 35.99
            ],
            [
                'name' => 'Life of Pi',
                'price' => 3299, // RM 32.99
            ],
            [
                'name' => 'The Book Thief',
                'price' => 3799, // RM 37.99
            ],
            [
                'name' => 'One Hundred Years of Solitude',
                'price' => 4199, // RM 41.99
            ],
            [
                'name' => 'The Alchemist',
                'price' => 2999, // RM 29.99
            ],
            [
                'name' => 'Dune',
                'price' => 4599, // RM 45.99
            ],
        ];

        foreach ($books as $bookData) {
            Product::firstOrCreate(
                ['name' => $bookData['name']], // Check by name to avoid duplicates
                array_merge($bookData, [
                    'category_id' => $bookCategory->id,
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Created Books category and 20 book products successfully!');
    }
}
