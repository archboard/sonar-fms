<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\HeadingRowImport;

trait IsFileImport
{
    public function getAbsolutePathAttribute(): string
    {
        return Storage::path($this->file_path);
    }

    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    public function getHeadersAttribute(): array
    {
        if (!$this->file_path) {
            return [];
        }

        $workbook = (new HeadingRowImport($this->heading_row))
            ->toArray($this->file_path);
        $sheets = Arr::first($workbook);

        return Arr::first($sheets);
    }

    public function getImportedRecordsAttribute($value): int
    {
        return $value ?? 0;
    }

    public function getFailedRecordsAttribute($value): int
    {
        return $value ?? 0;
    }
}
