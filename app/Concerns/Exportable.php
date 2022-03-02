<?php

namespace App\Concerns;

use App\Http\Requests\RecordsExportRequest;
use App\Models\RecordExport;
use Illuminate\Database\Eloquent\Builder as ModelBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

interface Exportable
{
    public static function getExportHeadings(): array;

    public function getExportRow(): array;

    public static function getExportQuery(RecordExport $export): Builder|ModelBuilder|Relation;
}
