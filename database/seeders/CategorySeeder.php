<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => ['id' => 'Seni Epoksi', 'en' => 'Epoxy Art'],
                'description' => ['id' => 'Produk seni berbasis resin epoksi', 'en' => 'Epoxy resin based art products'],
            ],
            [
                'name'        => ['id' => 'Dekorasi Rumah', 'en' => 'Home Decor'],
                'description' => ['id' => 'Dekorasi interior rumah', 'en' => 'Interior home decorations'],
            ],
            [
                'name'        => ['id' => 'Aksesori', 'en' => 'Accessories'],
                'description' => ['id' => 'Aksesori dan perhiasan epoksi', 'en' => 'Epoxy accessories and jewelry'],
            ],
            [
                'name'        => ['id' => 'Hadiah & Souvenir', 'en' => 'Gifts & Souvenirs'],
                'description' => ['id' => 'Produk sebagai hadiah atau souvenir', 'en' => 'Products as gifts or souvenirs'],
            ],
            [
                'name'        => ['id' => 'Custom Order', 'en' => 'Custom Order'],
                'description' => ['id' => 'Produk pesanan khusus sesuai keinginan', 'en' => 'Custom made products to your specification'],
            ],
        ];

        foreach ($categories as $i => $data) {
            Category::firstOrCreate(
                ['slug' => Str::slug($data['name']['id'])],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'is_active'   => true,
                    'sort_order'  => $i + 1,
                ]
            );
        }

        $this->command->info('✅ '.count($categories).' kategori seeded.');
    }
}
