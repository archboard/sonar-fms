<?php

namespace App\Models;

use App\Rules\InvoiceImportAmountOrPercentage;
use App\Rules\InvoiceImportMap;
use App\Imports\InvoiceImport as ExcelInvoiceImport;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\HeadingRowImport;

/**
 * @mixin IdeHelperInvoiceImport
 */
class InvoiceImport extends Model
{
    use HasResource;
    use BelongsToSchool;
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'mapping' => 'json',
        'results' => 'json',
        'total_records' => 'int',
        'imported_records' => 'int',
        'failed_records' => 'int',
        'heading_row' => 'int',
        'starting_row' => 'int',
        'imported_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
        });
    }

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

    public function getImportedRecordsAttribute($value)
    {
        return $value ?? 0;
    }

    public function getFailedRecordsAttribute($value)
    {
        return $value ?? 0;
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'import_id');
    }

    public function getExcelImport(): ExcelInvoiceImport
    {
        return new ExcelInvoiceImport($this);
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

    public function getMappingValidator(): \Illuminate\Validation\Validator
    {
        $mapping = $this->mapping ?? [];

        return Validator::make($mapping, [
            'student_attribute' => ['required', Rule::in(['sis_id', 'student_number', 'email'])],
            'student_column' => 'required',
            'title' => new InvoiceImportMap('required', true),
            'description' => new InvoiceImportMap('nullable'),
            'due_at' => new InvoiceImportMap('nullable'),
            'available_at' => new InvoiceImportMap('nullable'),
            'term_id' => new InvoiceImportMap([
                'nullable',
                Rule::in($this->school->terms->pluck('id')),
            ]),
            'notify' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.fee_id' => new InvoiceImportMap([
                'nullable',
                Rule::in($this->school->fees->pluck('id')),
            ]),
            'items.*.name' => new InvoiceImportMap('required', true),
            'items.*.amount_per_unit' => new InvoiceImportMap('required|integer', true),
            'items.*.quantity' => new InvoiceImportMap('required|integer', true),
            'scholarships' => 'array',
            'scholarships.*.scholarship_id' => new InvoiceImportMap([
                'nullable',
                Rule::in($this->school->scholarships->pluck('id')),
            ]),
            'scholarships.*.name' => new InvoiceImportMap('required', true),
            'scholarships.*.use_amount' => 'required|boolean',
            // required_without:scholarships.*.percentage
            'scholarships.*.amount' => [
                new InvoiceImportMap('nullable|integer'),
                new InvoiceImportAmountOrPercentage($mapping, 'percentage'),
            ],
            // required_without:scholarships.*.amount
            'scholarships.*.percentage' => [
                new InvoiceImportMap('nullable|numeric|max:100'),
                new InvoiceImportAmountOrPercentage($mapping, 'amount'),
            ],
            // I don't think this is needed here, we can set it manually
            'scholarships.*.resolution_strategy' => new InvoiceImportMap([
                'nullable',
                'required_with:scholarships.*.amount,scholarships.*.percentage',
                Rule::in(array_keys(Scholarship::getResolutionStrategies())),
            ]),
            'scholarships.*.applies_to' => 'array',
            'payment_schedules' => 'array',
            'payment_schedules.*.terms' => 'array',
            'payment_schedules.*.terms.*.use_amount' => 'required|boolean',
            // required_without:payment_schedules.*.terms.*.percentage
            'payment_schedules.*.terms.*.amount' => [
                new InvoiceImportMap('nullable|integer'),
                new InvoiceImportAmountOrPercentage($mapping, 'percentage'),
            ],
            // required_without:payment_schedules.*.terms.*.amount
            'payment_schedules.*.terms.*.percentage' => [
                new InvoiceImportMap('nullable|numeric|max:100'),
                new InvoiceImportAmountOrPercentage($mapping, 'amount'),
            ],
            'payment_schedules.*.terms.*.due_at' => new InvoiceImportMap('nullable|date'),
        ]);
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

    public static function storeFile(UploadedFile $file, School $school): string
    {
        $now = now()->format('U') . '-' . Str::random(8);

        return $file->storeAs(
            "imports/{$school->id}/{$now}",
            $file->getClientOriginalName()
        );
    }

    public function rollBack()
    {
        ray('rollback', $this->invoices()->delete());

        // Reset some properties
        $this->update([
            'rolled_back_at' => now(),
            'imported_at' => null,
            'failed_records' => 0,
            'imported_records' => 0,
            'results' => null,
        ]);
    }
}
