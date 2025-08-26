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

        $books = [
            [
                'name' => 'Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!',
                'description' => 'Sebuah panduan lengkap untuk memahami seni bercinta.',
                'price' => 5000,
                'is_featured' => true,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Diari Healing Yang Mendewasakan',
                'description' => 'Sebuah catatan perjalanan penyembuhan yang mendalam.',
                'price' => 3499,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Kasihi Puteri: The Untold Story of Daughterhood',
                'description' => 'Sebuah kisah yang menyentuh tentang hubungan antara ibu dan anak perempuan.',
                'price' => 2799,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Ini Rupanya Sebab Dia Terasa Dengan Kita, Senangnya Lahai Nak Baiki Lepas Tahu Cara Ni!',
                'description' => 'Sebuah panduan untuk memahami perasaan dan emosi dalam hubungan.',
                'price' => 3199,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Ini Rupanya Pekara Tak Boleh Buat Pada Anak Bila Ada Konflik Dengan Pasangan. Kenapalah Takde Orang Bagitahu Sebelum Ni?',
                'description' => 'Sebuah panduan untuk mengelakkan kesilapan dalam mendidik anak semasa konflik.',
                'price' => 2899,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Macam Ini Rupanya Cara Cakap Dengan Dia, Senangnya Lahai!',
                'description' => 'Sebuah panduan untuk berkomunikasi dengan pasangan.',
                'price' => 2699,
                'category_id' => $bookCategory->id
            ],
            [
                'name' => 'Kitab KKDI',
                'description' => 'Sebuah panduan lengkap untuk memahami konsep KKDI.',
                'price' => 5999,
                'category_id' => $bookCategory->id
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
