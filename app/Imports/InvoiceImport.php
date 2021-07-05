<?php

namespace App\Imports;

use App\Models\InvoiceImport as InvoiceImportModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class InvoiceImport implements ToCollection, WithHeadingRow, WithStartRow, WithCalculatedFormulas
{
    use Importable;

    public InvoiceImportModel $import;

    public function __construct(InvoiceImportModel $import)
    {
        $this->import = $import;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        ray($collection);
    }

    public function headingRow(): int
    {
        return $this->import->heading_row;
    }

    public function startRow(): int
    {
        return $this->import->starting_row;
    }
}
