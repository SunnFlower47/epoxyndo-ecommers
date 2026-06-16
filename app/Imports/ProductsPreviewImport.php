<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsPreviewImport implements ToCollection, WithHeadingRow
{
    public int $validCount = 0;
    public int $invalidCount = 0;

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check basic validation: 'nama_produk' and 'harga' must be present
            if (isset($row['nama_produk']) && !empty($row['nama_produk']) && isset($row['harga'])) {
                $this->validCount++;
            } else {
                $this->invalidCount++;
            }
        }
    }
}
