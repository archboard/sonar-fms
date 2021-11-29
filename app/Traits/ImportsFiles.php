<?php

namespace App\Traits;

use App\Imports\FileImport;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Maatwebsite\Excel\HeadingRowImport;

trait ImportsFiles
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

    #[Pure] public function getExcelImport(): FileImport
    {
        return new FileImport($this);
    }

    public function getImportContents(): Collection
    {
        $sheets = $this->getExcelImport()
            ->toCollection($this->absolute_path);

        // Don't support multiple sheets,
        // just grab the first sheet
        return $sheets->first();
    }

    public function setTotalRecords(): static
    {
        $this->total_records = $this->getImportContents()
            ->count();

        return $this;
    }

    public static function storeFile(UploadedFile $file, School $school): string
    {
        $now = now()->format('U') . '-' . Str::random(8);

        return $file->storeAs(
            "imports/{$school->id}/{$now}",
            $file->getClientOriginalName()
        );
    }

    public function getMappingValidationErrors(): array
    {
        return $this->getMappingValidator()
            ->errors()
            ->toArray();
    }

    public function hasValidMapping(): bool
    {
        return $this->getMappingValidator()
            ->passes();
    }
}
