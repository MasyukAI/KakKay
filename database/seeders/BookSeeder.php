<?php

declare(strict_types=1);

namespace Database\Seeders;

use AIArmada\Products\Enums\ProductStatus;
use AIArmada\Products\Models\Category;
use AIArmada\Products\Models\Product;
use Illuminate\Database\Seeder;

final class BookSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Books category using commerce package model
        $bookCategory = Category::firstOrCreate(
            ['slug' => 'books'],
            [
                'name' => 'Books',
                'is_visible' => true,
            ]
        );

        $books = [
            [
                'name' => 'Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!',
                'slug' => 'cara-bercinta',
                'description' => 'Panduan ajaib yang membongkar rahsia bercinta — praktikal, menyeronokkan dan penuh kejutan hati.',
                'price' => 5000,
                'is_featured' => true,
                'weight' => 0.35, // kg
                'length' => 21.0, // cm
                'width' => 14.8,
                'height' => 1.5,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Diari Healing Yang Mendewasakan',
                'slug' => 'diari-healing',
                'description' => 'Diari intim yang menuntunmu melalui detik-detik rapuh menjadi kuasa — penyembuhan yang terasa seperti penemuan semula.',
                'price' => 3499,
                'weight' => 0.25,
                'length' => 19.0,
                'width' => 13.0,
                'height' => 1.2,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Kasihi Puteri: The Untold Story of Daughterhood',
                'slug' => 'kasihi-puteri',
                'description' => 'Sebuah kisah berbisik tentang cinta yang diwariskan — lapisan emosi, rahsia, dan keberanian seorang puteri.',
                'price' => 2799,
                'weight' => 0.28,
                'length' => 21.0,
                'width' => 14.8,
                'height' => 1.3,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Ini Rupanya Sebab Dia Terasa Dengan Kita, Senangnya Lahai Nak Baiki Lepas Tahu Cara Ni!',
                'slug' => 'sebab-terasa',
                'description' => 'Manual halus untuk meneroka mengapa hati terluka — penuh petunjuk untuk membaiki dan memahami sendiri.',
                'price' => 3199,
                'weight' => 0.32,
                'length' => 21.0,
                'width' => 14.8,
                'height' => 1.4,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Ini Rupanya Pekara Tak Boleh Buat Pada Anak Bila Ada Konflik Dengan Pasangan. Kenapalah Takde Orang Bagitahu Sebelum Ni?',
                'slug' => 'tak-boleh-cakap',
                'description' => 'Panduan praktikal dan penuh empati untuk menjaga anak ketika dunia dewasa bergoncang — langkah demi langkah yang menenangkan.',
                'price' => 2899,
                'weight' => 0.30,
                'length' => 21.0,
                'width' => 14.8,
                'height' => 1.4,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Macam Ini Rupanya Cara Cakap Dengan Dia, Senangnya Lahai!',
                'slug' => 'cara-cakap',
                'description' => 'Seni berbicara yang membuat kata-kata biasa menjadi jambatan: teknik mudah untuk didengari dan dicintai.',
                'price' => 2699,
                'weight' => 0.22,
                'length' => 19.0,
                'width' => 13.0,
                'height' => 1.1,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Rahsia Mengenali Potensi Anak KKDI',
                'slug' => 'potensi-anak',
                'description' => 'Kunci untuk menemui potensi terpendam anak — pendekatan KKDI yang membangkitkan rasa ingin tahu dan percaya diri.',
                'price' => 5999,
                'is_featured' => true,
                'weight' => 0.45,
                'length' => 24.0,
                'width' => 17.0,
                'height' => 2.0,
                'requires_shipping' => true,
            ],
            [
                'name' => 'Kitab KKDI',
                'slug' => 'kitab-kkdi',
                'description' => 'Kitab panduan KKDI yang merangkum teori kepada latihan nyata — untuk guru, ibu bapa, dan pencari bakat kecil.',
                'price' => 5999,
                'is_featured' => true,
                'weight' => 0.50,
                'length' => 24.0,
                'width' => 17.0,
                'height' => 2.2,
                'requires_shipping' => true,
            ],
        ];

        foreach ($books as $bookData) {
            $product = Product::firstOrCreate(
                ['slug' => $bookData['slug']],
                array_merge($bookData, [
                    'status' => ProductStatus::Active,
                    'currency' => 'MYR',
                    'weight_unit' => 'kg',
                    'dimension_unit' => 'cm',
                ])
            );

            // Attach to category if not already attached
            if (! $product->categories()->where('category_id', $bookCategory->id)->exists()) {
                $product->categories()->attach($bookCategory->id);
            }
        }
    }
}
