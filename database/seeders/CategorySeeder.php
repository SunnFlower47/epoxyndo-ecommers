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
            ['name' => 'Highway', 'children' => [
                'Asphalt Instant', 'Concrete Instant', 'Injection Concrete', 'Road Marking', 'Seal Coat',
            ]],
            ['name' => 'Sport & Decorative', 'children' => [
                'Wood & Decorative', 'Stone', 'Sports', 'Bonding',
            ]],
            ['name' => 'Oil & Gas', 'children' => [
                'Repair Pipe', 'Chemical Heat Resistant', 'Coating & Protection',
            ]],
            ['name' => 'Civil Construction', 'children' => [
                'Repair Concrete', 'Civil Grouting', 'Heavy Duty Coating', 'Metal Coating',
            ]],
            ['name' => 'Transportation', 'children' => [
                'Body Coating', 'Brake Pads',
            ]],
            ['name' => 'Flooring (Epoxy, PU, Acrylic, Etc)', 'children' => [
                'Epoxy Flooring', 'Repair Floor',
            ]],
            ['name' => 'Waterproofing',              'children' => []],
            ['name' => 'Composite',                  'children' => []],
            ['name' => 'Grouting',                   'children' => []],
            ['name' => 'Chemical Resistant',         'children' => []],
            ['name' => 'Marine & Underwater', 'children' => [
                'Marine Coating', 'Underwater Coating',
            ]],
            ['name' => 'Sealant & Expansion Joint',  'children' => []],
            ['name' => 'Food Grade',                 'children' => []],
            ['name' => 'Concrete',                   'children' => []],
            ['name' => 'Asphalt',                    'children' => []],
            ['name' => 'Patching & Injection', 'children' => [
                'Asphalt Patching', 'Injection Products',
            ]],
        ];

        $sort = 1;
        foreach ($categories as $data) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => ['id' => $data['name'], 'en' => $data['name']],
                    'description'=> ['id' => null, 'en' => null],
                    'slug'       => Str::slug($data['name']),
                    'is_active'  => true,
                    'sort_order' => $sort++,
                    'parent_id'  => null,
                ]
            );

            $childSort = 1;
            foreach ($data['children'] as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($parent->slug . '-' . $childName)],
                    [
                        'parent_id'  => $parent->id,
                        'name'       => ['id' => $childName, 'en' => $childName],
                        'description'=> ['id' => null, 'en' => null],
                        'slug'       => Str::slug($parent->slug . '-' . $childName),
                        'is_active'  => true,
                        'sort_order' => $childSort++,
                    ]
                );
            }

            $childCount = count($data['children']);
            $this->command->info('✅ ' . $data['name'] . ($childCount ? " ({$childCount} subcategory)" : ''));
        }

        $this->command->info('');
        $this->command->info('🎉 Semua kategori Epoxyndo berhasil dibuat!');
    }
}
