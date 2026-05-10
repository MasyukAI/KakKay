<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::forget('home.products');
});

it('renders the redesigned home page with its key sections', function () {
    $product = Product::factory()->create([
        'name' => 'Karya Ujian Home',
        'slug' => 'cara-bercinta',
        'description' => 'Bimbingan hati yang lembut dan praktikal.',
        'price' => 5000,
        'status' => 'active',
        'is_featured' => true,
    ]);

    Product::factory()->create([
        'name' => 'Diari Healing Yang Mendewasakan',
        'slug' => 'diari-healing',
        'description' => 'Panduan untuk sembuh dengan lembut.',
        'price' => 3499,
        'status' => 'active',
        'is_featured' => false,
    ]);

    Product::factory()->create([
        'name' => 'Kasihi Puteri',
        'slug' => 'kasihi-puteri',
        'description' => 'Sebuah kisah tentang kasih dan keberanian.',
        'price' => 2799,
        'status' => 'active',
        'is_featured' => false,
    ]);

    $response = $this->get(route('home'));
    $content = $response->getContent();

    $response->assertSuccessful();
    $response->assertSee($product->name);
    $response->assertSeeText('Tiga cara untuk mula perjalanan baharu');
    $response->assertSeeText('Saya di sini untuk menemani perjalanan anda.');
    $response->assertSeeText('Koleksi Buku & Jurnal');
    $response->assertSeeText('Kadang-kadang, kita cuma perlukan seseorang untuk mendengar & membimbing.');
    $response->assertSeeText('Sedia untuk versi terbaik diri anda?');
    $response->assertSeeText('Hubungi');

    expect($content)
        ->toContain('x-data="bookShelfCarousel()"')
        ->toContain('data-book-carousel-prev')
        ->toContain('data-book-carousel-track')
        ->toContain('data-book-carousel-next')
        ->toContain('aria-label="Lihat buku seterusnya"');

    expect(mb_substr_count($content, 'data-book-carousel-slide'))->toBe(3);
});

it('prefers cara bercinta as the featured book on the home page', function () {
    Product::factory()->create([
        'name' => 'Rahsia Mengenali Potensi Anak KKDI',
        'slug' => 'potensi-anak',
        'description' => 'Panduan KKDI untuk mengenal potensi anak.',
        'price' => 5999,
        'status' => 'active',
        'is_featured' => true,
    ]);

    $featuredProduct = Product::factory()->create([
        'name' => 'Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!',
        'slug' => 'cara-bercinta',
        'description' => 'Panduan ajaib yang membongkar rahsia bercinta — praktikal, menyeronokkan dan penuh kejutan hati.',
        'price' => 5000,
        'status' => 'active',
        'is_featured' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertSeeTextInOrder([
        'Buku terlaris',
        $featuredProduct->name,
        $featuredProduct->description,
    ]);
});
