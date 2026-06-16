<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductsImport implements ToModel, WithChunkReading, WithHeadingRow, ShouldQueue
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row['nama_produk']) || empty($row['nama_produk'])) {
            return null;
        }

        return new Product([
            'name'        => $row['nama_produk'],
            'slug'        => Str::slug($row['nama_produk']) . '-' . uniqid(),
            'price'       => $row['harga'] ?? 0,
            'stock'       => $row['stok'] ?? 0,
            'weight'      => $row['berat'] ?? 1000,
            'sku'         => $row['sku'] ?? null,
            'barcode'     => $row['barcode'] ?? null,
            'is_active'   => isset($row['aktif']) ? (strtolower($row['aktif']) == 'ya') : true,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
