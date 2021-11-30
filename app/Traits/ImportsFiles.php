<?php

namespace App\Traits;

use App\Exceptions\InvalidImportFileTypeException;
use App\Http\Requests\CreateFileImportRequest;
use App\Http\Requests\UpdateFileImportRequest;
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

    public function storeFile(UploadedFile $file, School $school): string
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

    public function createFromRequest(CreateFileImportRequest $request): static
    {
        $data = $request->validated();
        $uploadedFile = $request->getFile();

        $this->forceFill([
            'user_uuid' => $request->user()->id,
            'file_path' => $this->storeFile($uploadedFile, $request->school()),
            'heading_row' => $data['heading_row'],
            'starting_row' => $data['starting_row'],
        ]);

        try {
            $this->setTotalRecords()
                ->save();
        } catch (\ValueError $exception) {
            session()->flash('error', __('There was a problem reading the file. Please make sure it is not password protected and try again.'));
            throw new InvalidImportFileTypeException();
        }

        return $this;
    }

    public function updateFromRequest(UpdateFileImportRequest $request): static
    {
        $data = $request->validated();
        $fileData = Arr::first($data['files']);
        $this->fill(Arr::except($data, 'files'));

        // This key only exists if the file hasn't been changed
        if (!isset($fileData['existing'])) {
            /** @var UploadedFile $file */
            $file = $fileData['file'];

            if (!$file->isValid()) {
                session()->flash('error', __('Invalid file.'));
                throw new InvalidImportFileTypeException();
            }

            Storage::delete($this->file_path);
            Storage::deleteDirectory(dirname($this->file_path));
            $this->file_path = $this->storeFile($file, $request->school());
            $this->mapping_valid = $this->hasValidMapping();
            $this->setTotalRecords();
        }

        $this->save();

        session()->flash('success', __('Import updated successfully.'));

        return $this;
    }

    public function reset(): static
    {
        $this->update([
            'rolled_back_at' => now(),
            'imported_at' => null,
            'failed_records' => 0,
            'imported_records' => 0,
            'results' => null,
        ]);

        return $this;
    }
}
