<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Urutan penting! RolePermission harus sebelum AdminUser
     * karena assignRole() bergantung pada role yang sudah ada.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class, // 1. Buat roles terlebih dahulu
            AdminUserSeeder::class,       // 2. Buat admin dan assign role
            SettingsSeeder::class,        // 3. Konfigurasi default toko
            CategorySeeder::class,        // 4. Kategori produk awal
        ]);
    }
}
