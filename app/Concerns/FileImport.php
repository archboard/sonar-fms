<?php

namespace App\Concerns;

use App\Http\Requests\CreateFileImportRequest;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface FileImport
{
    public function getAbsolutePathAttribute(): string;

    public function getFileNameAttribute(): string;

    public function getHeadersAttribute(): array;

    public function getImportedRecordsAttribute($value): int;

    public function getFailedRecordsAttribute($value): int;

    public function getExcelImport(): \App\Imports\FileImport;

    public function getImportContents(): Collection;

    public function setTotalRecords(): static;

    public function createFromRequest(CreateFileImportRequest $request): static;

    public function storeFile(UploadedFile $file, School $school): string;

    public function getMappingValidator(): \Illuminate\Validation\Validator;

    public function getMappingValidationErrors(): array;

    public function hasValidMapping(): bool;

    public function reset(): static;

    public function rollBack(): static;
}
