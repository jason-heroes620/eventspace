<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;

class VendorsImport implements ToArray
{
    public function array(array $row)
    {
        return $row;
    }
}
