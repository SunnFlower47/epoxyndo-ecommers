<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize
{
    use Exportable;

    public function query()
    {
        return Product::query()->with('category');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kategori',
            'Nama Produk',
            'Slug',
            'Harga',
            'Stok',
            'Berat',
            'SKU',
            'Barcode',
            'Aktif',
        ];
    }

    /**
     * @param Product $product
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->category ? $product->category->name : '',
            $product->name,
            $product->slug,
            $product->price,
            $product->stock,
            $product->weight,
            $product->sku,
            $product->barcode,
            $product->is_active ? 'Ya' : 'Tidak',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
