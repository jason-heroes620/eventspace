<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new VendorsImport(),
            1 => new ProductsImportList()
        ];
    }
}
