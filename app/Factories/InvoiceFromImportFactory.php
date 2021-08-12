<?php

namespace App\Factories;

use App\Exceptions\InvalidImportMapValue;
use App\Models\Invoice;
use App\Models\InvoiceImport;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use App\Utilities\NumberUtility;
use Brick\Money\Money;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InvoiceFromImportFactory extends InvoiceFactory
{
    protected ?InvoiceImport $import;
    protected Collection $contents;
    protected array $results = [];
    protected array $warnings = [];
    protected Collection $currentRow;
    protected int $currentRowNumber = 0;
    protected int $failedRecords = 0;
    protected int $importedRecords = 0;
    protected bool $attributesBuilt = false;
    protected bool $asModels = false;
    protected string $userNow = '';

    protected Collection $terms;
    protected Collection $fees;
    protected Collection $scholarships;

    protected Collection $models;

    // These are the collections that store the attributes
    // that need to be stored in the db
    protected Collection $localInvoices;
    protected Collection $localInvoiceItems;
    protected Collection $localInvoiceScholarships;
    protected Collection $localItemScholarshipPivot;
    protected Collection $localInvoicePaymentSchedules;
    protected Collection $localInvoicePaymentTerms;

    public static function make(InvoiceImport $import = null): static
    {
        return (new static)
            ->setInvoiceImport($import)
            ->resetLocalStores()
            ->setStudents();
    }

    public function setInvoiceImport(InvoiceImport $import = null): static
    {
        $this->import = $import;
        $this->contents = $import->getImportContents();
        $this->school = $import->school;
        $this->user = $import->user;
        $this->terms = $this->school->terms->keyBy('sis_assigned_id');
        $this->fees = $this->school->fees->keyBy('id');
        $this->scholarships = $this->school->scholarships->keyBy('id');
        $this->userNow = now($this->user->timezone)->format('Y-m-d');

        return $this;
    }

    public function setStudents(): static
    {
        $values = $this->contents
            ->pluck($this->getMapField('student_column'))
            ->filter(fn ($value) => !is_null($value));

        $this->students = $this->import->school->students()
            ->whereIn($this->getMapField('student_attribute'), $values)
            ->get()
            ->keyBy($this->getMapField('student_attribute'));

        return $this;
    }

    public function asModels(): static
    {
        $this->asModels = true;
        $this->models = collect();

        return $this;
    }

    protected function resetLocalStores(): static
    {
        $this->localInvoices = collect();
        $this->localInvoiceItems = collect();
        $this->localInvoiceScholarships = collect();
        $this->localItemScholarshipPivot = collect();
        $this->localInvoicePaymentSchedules = collect();
        $this->localInvoicePaymentTerms = collect();

        return $this;
    }

    protected function addResult(string $result, bool $successful = true)
    {
        $property = $successful ? 'importedRecords' : 'failedRecords';
        $this->{$property}++;

        $this->results[] = [
            'row' => $this->currentRowNumber,
            'successful' => $successful,
            'result' => $result,
            'student' => optional($this->getStudentForCurrentRow())->full_name,
            'warnings' => $this->warnings,
        ];

        $this->warnings = [];
    }

    protected function getStudentForCurrentRow(): ?Student
    {
        return $this->students->get(
            $this->currentRow->get($this->getMapField('student_column'))
        );
    }

    protected function addWarning(string $message)
    {
        $this->warnings[] = $message;
    }

    protected function getMapField(string $key)
    {
        return Arr::get($this->import->mapping, $key) ?? null;
    }

    /**
     * Converts Excel's number format to a
     * Carbon instance and returns the date/time string
     *
     * @param $value
     * @return string|null
     */
    protected function convertDateTime($value): ?string
    {
        if (is_numeric($value)) {
            $date = Carbon::create(1900, 1, 1, 0, 0, 0, $this->user->timezone);
            $days = floatval($value) - 2;
            $hours = $days * 24;
            $minutes = $hours * 60;
            return $date->addMinutes($minutes)
                ->roundUnit('minute', 15)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        }

        try {
            return Carbon::parse($value, $this->user->timezone)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        } catch (InvalidFormatException $exception) {
            return null;
        }
    }

    /**
     * Converts Excel's number format to a
     * Carbon instance and returns the date string
     *
     * @param $value
     * @return string|null
     */
    protected function convertDate($value): ?string
    {
        if (is_numeric($value)) {
            $date = Carbon::create(1900, 1, 1, 0, 0, 0, $this->user->timezone);
            $days = floatval($value) - 2;
            $hours = $days * 24;
            $minutes = $hours * 60;
            return $date->addMinutes($minutes)
                ->toDateString();
        }

        try {
            return Carbon::parse($value, $this->user->timezone)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        } catch (InvalidFormatException $exception) {
            return null;
        }
    }

    protected function convertCurrency($value): ?int
    {
        $sanitized = NumberUtility::sanitizeNumber($value);

        try {
            return Money::of($sanitized, $this->school->currency->code)
                ->getMinorAmount()
                ->toInt();
        } catch (\Exception $exception) {
            return null;
        }
    }

    protected function convertInt($value): int
    {
        return (int) $value;
    }

    protected function convertPercentage($value)
    {
        return NumberUtility::convertPercentageFromUser($value);
    }

    protected function convertTerm($value)
    {
        if (empty($value)) {
            return null;
        }

        // If they provided the sis assigned id of the term
        // Look up in the dictionary and return the local id
        if ($this->terms->has($value)) {
            return $this->terms->get($value)->id;
        }

        // If this term id does indeed exist
        // just return the original value, otherwise leave null
        if ($this->terms->firstWhere('id', $value)) {
            return $value;
        }

        // __('Could not find term, leaving blank')
        $this->addWarning('Could not find term, leaving blank');

        return null;
    }

    protected function convertFee($value)
    {
        if (empty($value)) {
            return null;
        }

        $fee = $this->fees->get($value);

        if (!$fee) {
            // __('Invalid fee ID, fee does not exist')
            $this->addWarning('Invalid fee ID, fee does not exist');
            return null;
        }

        return $fee->id;
    }

    protected function convertScholarship($value)
    {
        if (empty($value)) {
            return null;
        }

        $scholarship = $this->scholarships->get($value);

        if (!$scholarship) {
            // __('Invalid scholarship ID, scholarship does not exist')
            $this->addWarning('Invalid scholarship ID, scholarship does not exist');
            return null;
        }

        return $scholarship->id;
    }

    protected function getMapValue(string $key, string $conversion = null)
    {
        $mapField = $this->getMapField($key);

        if (!is_array($mapField)) {
            return $mapField;
        }

        if ($mapField['isManual']) {
            return $mapField['value'];
        }

        $value = $this->currentRow->get($mapField['column']);

        if ($conversion) {
            $method = Str::camel('convert ' . $conversion);

            if (method_exists($this, $method)) {
                return $this->{$method}($value);
            }
        }

        return $value;
    }

    /**
     * Sets the most attributes we can before knowing line item
     * and scholarship details
     *
     * @return array
     * @throws InvalidImportMapValue
     */
    protected function getInvoiceAttributes(): array
    {
        $student = $this->getStudentForCurrentRow();

        if (!$student) {
            // __('Could not find student')
            throw new InvalidImportMapValue('Could not find student');
        }

        $attributes = [
            'batch_id' => $this->batchId,
            'tenant_id' => $this->school->tenant_id,
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'user_id' => $this->user->id,
            'import_id' => $this->import->id,
            'uuid' => $this->uuid(),
            'title' => $this->getMapValue('title'),
            'description' => $this->getMapValue('description'),
            'invoice_date' => $this->getMapValue('invoice_date', 'date') ?? $this->userNow,
            'due_at' => $this->getMapValue('due_at', 'date time'),
            'available_at' => $this->getMapValue('available_at', 'date time'),
            'term_id' => $this->getMapValue('term_id', 'term'),
            'notify' => $this->getMapValue('notify'),
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        if ($attributes['notify']) {
            $attributes['notified_at'] = null;
            $attributes['notify_at'] = $this->notifyAt;
        }

        return $attributes;
    }

    /**
     * This builds the invoice items for an invoice
     * and returns the invoice item attributes keyed
     * by the id they were give on the frontend so we
     * can map to scholarship relationships
     *
     * @param string $invoiceUuid
     * @return Collection
     * @throws InvalidImportMapValue
     */
    protected function buildInvoiceItems(string $invoiceUuid): Collection
    {
        /** @var Collection $items */
        $items = collect($this->getMapField('items'))
            ->reduce(function (Collection $items, array $item, int $index) use ($invoiceUuid) {
                // If there isn't an amount value configured, do not add this item
                if (blank($this->getMapValue("items.{$index}.amount_per_unit"))) {
                    return $items;
                }

                $perUnit = $this->getMapValue("items.{$index}.amount_per_unit", 'currency');
                $quantity = $this->getMapValue("items.{$index}.quantity", 'int');

                if (!is_int($perUnit)) {
                    ray('invalid value', $perUnit)->red();
                    // __('Invalid line item amount')
                    throw new InvalidImportMapValue('Invalid line item amount');
                }

                if (!is_int((int) $quantity)) {
                    ray('invalid value', $quantity)->red();
                    // __('Invalid line item quantity')
                    throw new InvalidImportMapValue('Invalid line item quantity');
                }

                if ($perUnit > 0 && $quantity > 0) {
                    $items->put($item['id'], [
                        'batch_id' => $this->batchId,
                        'invoice_uuid' => $invoiceUuid,
                        'uuid' => $this->uuid(),
                        'fee_id' => $this->getMapValue("items.{$index}.fee_id", 'fee'),
                        'name' => $this->getMapValue("items.{$index}.name") ?? 'Line item',
                        'amount_per_unit' => $perUnit,
                        'quantity' => $quantity,
                        'amount' => $perUnit * $quantity,
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ]);
                }

                return $items;
            }, collect());

        if ($items->isEmpty()) {
            // __('No invoice items exist')
            throw new InvalidImportMapValue('No invoice items exist');
        }

        return $items;
    }

    /**
     * Adds invoice scholarship attributes to the collection
     * and returns the total discount amount for all scholarships
     *
     * Scholarships are _optional_ parts of an invoice.
     * Therefore, if there is no amount or percentage
     * we won't add any scholarships for the row
     *
     * @param string $invoiceUuid
     * @param int $subtotal
     * @param Collection $invoiceItems
     * @return int
     */
    protected function buildInvoiceScholarships(string $invoiceUuid, int $subtotal, Collection $invoiceItems): int
    {
        return collect($this->getMapField('scholarships'))
            ->reduce(function (int $total, array $item, int $index) use ($invoiceUuid, $subtotal, $invoiceItems) {
                $amount = $this->getMapValue("scholarships.{$index}.amount", 'currency');
                $percentage = $this->getMapValue("scholarships.{$index}.percentage", 'percentage');

                // Don't process if there isn't an amount
                // or percentage since scholarships are optional
                if (
                    ($item['use_amount'] && empty($amount)) ||
                    (!$item['use_amount'] && empty($percentage))
                ) {
                    return $total;
                }

                $attributes = [
                    'batch_id' => $this->batchId,
                    'invoice_uuid' => $invoiceUuid,
                    'uuid' => $this->uuid(),
                    'scholarship_id' => $this->getMapValue("scholarships.{$index}.scholarship_id", 'scholarship'),
                    'name' => $this->getMapValue("scholarships.{$index}.name"),
                    'amount' => $item['use_amount'] ? $amount : null,
                    'percentage' => $item['use_amount'] ? null : $percentage,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $applicableSubtotal = $subtotal;

                if (
                    !empty($item['applies_to']) &&
                    count($item['applies_to']) !== $invoiceItems->count()
                ) {
                    // This is probably "bad practice" since the reduce
                    // callback adds items to the pivot collection (side effect),
                    // but I don't want to iterate the items again
                    $applicableSubtotal = collect($item['applies_to'])
                        ->reduce(function (int $total, string $id) use ($attributes, $invoiceItems) {
                            $invoiceItem = $invoiceItems->get($id);

                            $this->localItemScholarshipPivot->push([
                                'invoice_item_uuid' => $invoiceItem['uuid'],
                                'invoice_scholarship_uuid' => $attributes['uuid'],
                            ]);

                            return $total + $invoiceItem['amount'];
                        }, 0);
                }

                // Default the discount amount to the static amount
                $discount = $amount;

                // If we're not using amount calculate the discount
                // based on the appropriate subtotal
                if (!$item['use_amount']) {
                    $discount = $applicableSubtotal * $attributes['percentage'];
                }

                // Don't let the discount exceed the subtotal
                if ($discount > $applicableSubtotal) {
                    $discount = $applicableSubtotal;
                }

                $attributes['calculated_amount'] = $discount;
                $this->localInvoiceScholarships->push($attributes);

                return $total + $discount;
            }, 0);
    }

    /**
     * Payment schedules are also optional, like scholarships
     * If a row doesn't have any schedule values, skip creating
     * them for this row. Don't throw an exception and import the
     * rest of the invoice normally.
     *
     * @param string $invoiceUuid
     * @param int $amountDue
     * @return Collection
     */
    protected function buildPaymentSchedules(string $invoiceUuid, int $amountDue): Collection
    {
        return collect($this->getMapField('payment_schedules'))
            ->each(function (array $item, int $scheduleIndex) use ($invoiceUuid, $amountDue) {
                $scheduleUuid = $this->uuid();
                $scheduleAttributes = [
                    'uuid' => $scheduleUuid,
                    'batch_id' => $this->batchId,
                    'invoice_uuid' => $invoiceUuid,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                $scheduleAmount = collect($item['terms'])
                    ->reduce(function (
                        int $total,
                        array $term,
                        int $termIndex
                    ) use ($invoiceUuid, $scheduleUuid, $amountDue, $scheduleIndex) {
                        $amount = $this->getMapValue(
                            "payment_schedules.{$scheduleIndex}.terms.{$termIndex}.amount",
                            'currency'
                        );
                        $percentage = $this->getMapValue(
                            "payment_schedules.{$scheduleIndex}.terms.{$termIndex}.percentage",
                            'percentage'
                        );

                        // Don't process if the "use" value is empty
                        if (
                            ($term['use_amount'] && empty($amount)) ||
                            (!$term['use_amount'] && empty($percentage))
                        ) {
                            return $total;
                        }

                        $termAmountDue = $term['use_amount']
                            ? $amount
                            : $amountDue * $percentage;

                        $this->localInvoicePaymentTerms->push([
                            'uuid' => $this->uuid(),
                            'batch_id' => $this->batchId,
                            'invoice_uuid' => $invoiceUuid,
                            'invoice_payment_schedule_uuid' => $scheduleUuid,
                            'amount' => $amount,
                            'percentage' => $percentage,
                            'amount_due' => $termAmountDue,
                            'remaining_balance' => $termAmountDue,
                            'created_at' => $this->now,
                            'updated_at' => $this->now,
                        ]);

                        return $total + $termAmountDue;
                    }, 0);

                // If the amount is zero, it means no valid terms exist
                if ($scheduleAmount > 0) {
                    $scheduleAttributes['amount'] = $scheduleAmount;
                    $this->localInvoicePaymentSchedules->push($scheduleAttributes);
                }
            });
    }

    public function buildAttributes()
    {
        $this->contents->each(function (Collection $row, int $rowIndex) {
            $this->currentRow = $row;
            $this->currentRowNumber = $this->import->starting_row + $rowIndex;

            try {
                // Don't process if the student reference column is empty
                // Potentially later we would create a new student, but not now
                if (blank($row[$this->getMapField('student_column')])) {
                    // __('Missing student identifier value')
                    throw new InvalidImportMapValue('Missing student identifier value');
                }

                $invoiceAttributes = $this->getInvoiceAttributes();
                $invoiceUuid = $invoiceAttributes['uuid'];

                // Build the items
                $items = $this->buildInvoiceItems($invoiceUuid);
                $subtotal = $items->reduce(fn ($total, $item) => $total + $item['amount']);
                $this->localInvoiceItems = $this->localInvoiceItems->merge($items->values());

                // Build the scholarships
                $discount = $this->buildInvoiceScholarships($invoiceUuid, $subtotal, $items);

                $amountDue = $subtotal - $discount;

                if ($amountDue < 0) {
                    $amountDue = 0;
                }

                $invoiceAttributes['amount_due'] = $amountDue;
                $invoiceAttributes['remaining_balance'] = $amountDue;
                $invoiceAttributes['subtotal'] = $subtotal;
                $invoiceAttributes['discount_total'] = $discount > $subtotal
                    ? $subtotal
                    : $discount;

                // Build the payment schedules
                $this->buildPaymentSchedules($invoiceUuid, $amountDue);

                // After everything has been calculated add the invoice to the collection
                $this->localInvoices->push($invoiceAttributes);

                // Add the successful result
                $this->addResult($invoiceUuid);

                // Merge the local table data with the parent collections
                $this->commit();
            } catch (InvalidImportMapValue $exception) {
                ray($exception->getMessage())->red();
                $this->addResult($exception->getMessage(), false);
                $this->resetLocalStores();
            }
        });

        $this->attributesBuilt = true;
    }

    public function build(): Collection
    {
        if (!$this->attributesBuilt) {
            $this->buildAttributes();
        }

        $this->import->forceFill([
            'imported_records' => $this->importedRecords,
            'failed_records' => $this->failedRecords,
            'imported_at' => $this->asModels ? null : now(),
            'results' => $this->results,
        ]);

        ray($this->import);

        // If we're just building models, only return the import
        // and the models collection instead of storing the results
        if ($this->asModels) {
            return collect()->put('invoiceImport', $this->import)
                ->put('models', $this->models->keyBy('uuid'));
        }

        $storeResults = $this->store();
        $this->import->save();

        return $storeResults;
    }

    protected function commit()
    {
        if ($this->asModels) {
            $invoice = new Invoice($this->localInvoices->first());
            $invoice->setRelation('student', $this->getStudentForCurrentRow());
            $invoice->setRelation(
                'invoiceItems',
                $this->localInvoiceItems->map(fn ($item) => new InvoiceItem($item))
            );
            $invoice->setRelation(
                'invoiceScholarships',
                $this->localInvoiceScholarships->map(function ($item) {
                    $scholarship = new InvoiceScholarship($item);
                    $scholarship->setRelation(
                        'appliesTo',
                        $this->localInvoiceItems->filter(
                            fn ($i) => $this->localItemScholarshipPivot->contains('invoice_item_uuid', $i['uuid'])
                        )->map(fn ($i) => new InvoiceItem($i))
                    );

                    return $scholarship;
                })
            );
            $invoice->setRelation(
                'invoicePaymentSchedules',
                $this->localInvoicePaymentSchedules->map(function ($item) {
                    $schedule = new InvoicePaymentSchedule($item);
                    $schedule->setRelation(
                        'invoicePaymentTerms',
                        $this->localInvoicePaymentTerms->filter(
                            fn ($t) => $t['invoice_payment_schedule_uuid'] === $item['uuid']
                        )->map(fn ($t) => new InvoicePaymentTerm($t))
                    );

                    return $schedule;
                })
            );

            $this->models->push($invoice);
        } else {
            $this->invoices = $this->invoices->merge($this->localInvoices);
            $this->invoiceItems = $this->invoiceItems->merge($this->localInvoiceItems);
            $this->invoiceScholarships = $this->invoiceScholarships->merge($this->localInvoiceScholarships);
            $this->itemScholarshipPivot = $this->itemScholarshipPivot->merge($this->localItemScholarshipPivot);
            $this->invoicePaymentSchedules = $this->invoicePaymentSchedules->merge($this->localInvoicePaymentSchedules);
            $this->invoicePaymentTerms = $this->invoicePaymentTerms->merge($this->localInvoicePaymentTerms);
        }

        $this->resetLocalStores();
    }
}
