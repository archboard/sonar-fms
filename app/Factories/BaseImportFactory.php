<?php

namespace App\Factories;

use Illuminate\Support\Collection;

abstract class BaseImportFactory
{
    protected Collection $contents;
    protected Collection $results;
    protected Collection $currentRow;
    protected int $currentRowNumber = 0;
    protected int $failedRecords = 0;
    protected int $importedRecords = 0;
}
