<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class BookSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Books category
        $bookCategory = Category::firstOrCreate([
            'name' => 'Books',
        ]);

        $books = [
            [
                'name' => 'Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!',
                'slug' => 'cara-bercinta',
                'description' => 'Panduan ajaib yang membongkar rahsia bercinta — praktikal, menyeronokkan dan penuh kejutan hati.',
                'price' => 5000,
                'is_featured' => true,
                'category_id' => $bookCategory->id,
                'weight' => 350, // 350g typical paperback
                'length' => 210, // A5 size in mm
                'width' => 148,
                'height' => 15,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Diari Healing Yang Mendewasakan',
                'slug' => 'diari-healing',
                'description' => 'Diari intim yang menuntunmu melalui detik-detik rapuh menjadi kuasa — penyembuhan yang terasa seperti penemuan semula.',
                'price' => 3499,
                'category_id' => $bookCategory->id,
                'weight' => 250,
                'length' => 190,
                'width' => 130,
                'height' => 12,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Kasihi Puteri: The Untold Story of Daughterhood',
                'slug' => 'kasihi-puteri',
                'description' => 'Sebuah kisah berbisik tentang cinta yang diwariskan — lapisan emosi, rahsia, dan keberanian seorang puteri.',
                'price' => 2799,
                'category_id' => $bookCategory->id,
                'weight' => 280,
                'length' => 210,
                'width' => 148,
                'height' => 13,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Ini Rupanya Sebab Dia Terasa Dengan Kita, Senangnya Lahai Nak Baiki Lepas Tahu Cara Ni!',
                'slug' => 'sebab-terasa',
                'description' => 'Manual halus untuk meneroka mengapa hati terluka — penuh petunjuk untuk membaiki dan memahami sendiri.',
                'price' => 3199,
                'category_id' => $bookCategory->id,
                'weight' => 320,
                'length' => 210,
                'width' => 148,
                'height' => 14,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Ini Rupanya Pekara Tak Boleh Buat Pada Anak Bila Ada Konflik Dengan Pasangan. Kenapalah Takde Orang Bagitahu Sebelum Ni?',
                'slug' => 'tak-boleh-cakap',
                'description' => 'Panduan praktikal dan penuh empati untuk menjaga anak ketika dunia dewasa bergoncang — langkah demi langkah yang menenangkan.',
                'price' => 2899,
                'category_id' => $bookCategory->id,
                'weight' => 300,
                'length' => 210,
                'width' => 148,
                'height' => 14,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Macam Ini Rupanya Cara Cakap Dengan Dia, Senangnya Lahai!',
                'slug' => 'cara-cakap',
                'description' => 'Seni berbicara yang membuat kata-kata biasa menjadi jambatan: teknik mudah untuk didengari dan dicintai.',
                'price' => 2699,
                'category_id' => $bookCategory->id,
                'weight' => 220,
                'length' => 190,
                'width' => 130,
                'height' => 11,
                'is_digital' => false,
                'free_shipping' => false,
            ],
            [
                'name' => 'Rahsia Mengenali Potensi Anak KKDI',
                'slug' => 'potensi-anak',
                'description' => 'Kunci untuk menemui potensi terpendam anak — pendekatan KKDI yang membangkitkan rasa ingin tahu dan percaya diri.',
                'price' => 5999,
                'category_id' => $bookCategory->id,
                'weight' => 450, // Larger book
                'length' => 240,
                'width' => 170,
                'height' => 20,
                'is_digital' => false,
                'free_shipping' => true, // Premium book with free shipping
            ],
            [
                'name' => 'Kitab KKDI',
                'slug' => 'kitab-kkdi',
                'description' => 'Kitab panduan KKDI yang merangkum teori kepada latihan nyata — untuk guru, ibu bapa, dan pencari bakat kecil.',
                'price' => 5999,
                'category_id' => $bookCategory->id,
                'weight' => 500, // Heavy reference book
                'length' => 240,
                'width' => 170,
                'height' => 22,
                'is_digital' => false,
                'free_shipping' => true, // Premium book with free shipping
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
    }
}
