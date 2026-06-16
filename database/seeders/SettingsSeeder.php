<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'ppn_rate',          'value' => '12',                'type' => 'integer'],
            ['key' => 'store_name',         'value' => 'Epoxyndo Art Lestari', 'type' => 'string'],
            ['key' => 'store_email',        'value' => 'support@epoxyndo.com', 'type' => 'string'],
            ['key' => 'store_phone',        'value' => '+62-xxx-xxxx-xxxx', 'type' => 'string'],
            ['key' => 'warehouse_city',     'value' => 'Jakarta',           'type' => 'string'],
            ['key' => 'warehouse_lat',      'value' => '-6.2088',           'type' => 'float'],
            ['key' => 'warehouse_lng',      'value' => '106.8456',          'type' => 'float'],
            ['key' => 'free_shipping_min',  'value' => '500000',            'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }

        $this->command->info('✅ Settings seeded.');
    }
}
