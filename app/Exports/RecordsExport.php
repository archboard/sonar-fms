<?php

namespace App\Exports;

use App\Models\RecordExport;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecordsExport implements Responsable, FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public string $fileName;

    public function __construct(protected RecordExport $export)
    {
        $this->fileName = $this->export->file_name;
    }

    public function query(): Relation|EloquentBuilder|Builder
    {
        return $this->export->model::getExportQuery($this->export);
    }

    public function headings(): array
    {
        return $this->export->model::getExportHeadings();
    }

    /**
     * @param \App\Concerns\Exportable $row
     * @return array
     */
    public function map($row): array
    {
        return $row->getExportRow();
    }
}
