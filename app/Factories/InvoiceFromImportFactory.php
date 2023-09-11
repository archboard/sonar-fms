<?php

namespace App\Factories;

use App\Exceptions\InvalidImportMapValue;
use App\Models\Invoice;
use App\Models\InvoiceImport;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\InvoiceTaxItem;
use App\Models\Student;
use App\Traits\ConvertsExcelValues;
use App\Traits\GetsImportMappingValues;
use App\Utilities\NumberUtility;
use Brick\Money\Money;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InvoiceFromImportFactory extends InvoiceFactory
{
    use ConvertsExcelValues;
    use GetsImportMappingValues;

    protected ?InvoiceImport $import;
    protected Collection $contents;
    protected array $warnings = [];
    protected bool $attributesBuilt = false;
    protected bool $asModels = false;
    protected string $userNow = '';
    protected int $rowSubtotal = 0;
    protected int $rowDiscountTotal = 0;
    protected int $rowPreTaxSubtotal = 0;
    protected int $rowTaxDue = 0;
    protected string $rowInvoiceUuid = '';

    protected Collection $terms;
    protected Collection $fees;
    protected Collection $scholarships;

    protected Collection $models;

    // These are the collections that store the attributes
    // that need to be stored in the db
    protected Collection $localInvoices;
    protected Collection $localInvoiceItems;
    protected Collection $localInvoiceItemsKeyedById;
    protected Collection $localInvoiceScholarships;
    protected Collection $localItemScholarshipPivot;
    protected Collection $localInvoicePaymentSchedules;
    protected Collection $localInvoicePaymentTerms;
    protected Collection $localInvoiceTaxItems;

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
        $this->school = $import->school; // @phpstan-ignore-line
        $this->user = $import->user; // @phpstan-ignore-line
        $this->invoiceNumberPrefix = $this->school->getInvoiceNumberPrefix($this->user);
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
        $this->localInvoiceItemsKeyedById = collect();
        $this->localInvoiceScholarships = collect();
        $this->localItemScholarshipPivot = collect();
        $this->localInvoicePaymentSchedules = collect();
        $this->localInvoicePaymentTerms = collect();
        $this->localInvoiceTaxItems = collect();
        $this->rowSubtotal = 0;
        $this->rowDiscountTotal = 0;
        $this->rowPreTaxSubtotal = 0;
        $this->rowTaxDue = 0;

        return $this;
    }

    protected function addResult(string $result, bool $successful = true)
    {
        $property = $successful ? 'importedRecords' : 'failedRecords';
        $this->{$property}++;

        $this->results->push([
            'row' => $this->currentRowNumber,
            'successful' => $successful,
            'result' => $result,
            'student' => optional($this->getStudentForCurrentRow())->full_name,
            'warnings' => $this->warnings,
        ]);

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

    protected function convertTaxRate($value)
    {
        if ($this->collectingTax()) {
            if ($this->getMapValue('use_school_tax_defaults')) {
                return $this->school->tax_rate;
            }

            return NumberUtility::convertPercentageFromUser($value);
        }

        return 0;
    }

    protected function convertTaxLabel($value)
    {
        if ($this->collectingTax()) {
            if ($this->getMapValue('use_school_tax_defaults')) {
                return $this->school->tax_label;
            }

            return $value;
        }

        return null;
    }

    protected function collectingTax(): bool
    {
        return $this->school->collect_tax &&
            $this->getMapValue('apply_tax');
    }

    /**
     * Sets the invoice attributes.
     * This is done last after all items, scholarships,
     * and tax details have been figured out
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

        $gradeAdjust = $this->getMapValue('grade_level_adjustment', 'int', 0);
        $attributes = [
            'batch_id' => $this->batchId,
            'tenant_id' => $this->school->tenant_id,
            'school_id' => $this->school->id,
            'student_uuid' => $student->uuid,
            'user_uuid' => $this->user->uuid,
            'invoice_import_id' => $this->import->id,
            'uuid' => $this->rowInvoiceUuid,
            'invoice_number' => Invoice::generateInvoiceNumber($this->school->id, $this->invoiceNumberPrefix),
            'title' => $this->getMapValue('title'),
            'raw_title' => $this->getMapValue('title'),
            'description' => $this->getMapValue('description'),
            'invoice_date' => $this->getMapValue('invoice_date', 'date') ?? $this->userNow,
            'due_at' => $this->getMapValue('due_at', 'date time'),
            'available_at' => $this->getMapValue('available_at', 'date time'),
            'term_id' => $this->getMapValue('term_id', 'term'),
            'notify' => $this->getMapValue('notify'),
            'subtotal' => $this->rowSubtotal,
            'discount_total' => $this->rowDiscountTotal,
            'apply_tax' => $this->getMapValue('apply_tax', null, false),
            'apply_tax_to_all_items' => $this->getMapValue(key: 'apply_tax_to_all_items', default: true),
            'pre_tax_subtotal' => $this->rowPreTaxSubtotal,
            'tax_rate' => $this->getMapValue('tax_rate', 'tax rate'),
            'tax_label' => $this->getMapValue('tax_label', 'tax label'),
            'tax_due' => $this->rowTaxDue,
            'published_at' => $this->asDraft ? null : $this->now,
            'grade_level_adjustment' => $gradeAdjust,
            'grade_level' => $student->grade_level + $gradeAdjust,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        // If the title contains a bracket, do some dynamic processing on it
        if (str_contains($attributes['title'], '{')) {
            $term = $attributes['term_id']
                ? $this->terms->firstWhere('id', $attributes['term_id'])
                : null;

            // Compile invoice title if it contains a bracket
            $attributes['title'] = $this->school
                ->compileTemplate($attributes['title'], student: $student, term: $term);
        }

        $amountDue = $this->rowPreTaxSubtotal + $this->rowTaxDue;
        $attributes['amount_due'] = $amountDue;
        $attributes['remaining_balance'] = $amountDue;

        $relativeTaxRate = $amountDue > 0 ? $this->rowTaxDue / $amountDue : 0;
        $attributes['relative_tax_rate'] = round($relativeTaxRate, 8);

        if ($attributes['notify']) {
            $attributes['notified_at'] = null;
            $attributes['notify_at'] = $this->notifyAt;
        }

        return $attributes;
    }

    /**
     * This builds the invoice items for an invoice
     * and returns the invoice item attributes keyed
     * by the id they were give on the frontend so that we
     * can map to scholarship relationships
     *
     * @return Collection
     * @throws InvalidImportMapValue
     */
    protected function buildInvoiceItems(): Collection
    {
        /** @var Collection $items */
        $items = collect($this->getMapField('items'))
            ->reduce(function (Collection $items, array $item, int $index) {
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
                        'invoice_uuid' => $this->rowInvoiceUuid,
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
     * @return int
     */
    protected function buildInvoiceScholarships(): int
    {
        return collect($this->getMapField('scholarships'))
            ->reduce(function (int $total, array $item, int $index) {
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

                $name = trim($this->getMapValue("scholarships.{$index}.name"));

                if (empty($name)) {
                    $this->addWarning("Scholarship missing name. Please add a name value to the spreadsheet or enter a manual value.");
                    return $total;
                }

                $attributes = [
                    'batch_id' => $this->batchId,
                    'invoice_uuid' => $this->rowInvoiceUuid,
                    'uuid' => $this->uuid(),
                    'scholarship_id' => $this->getMapValue("scholarships.{$index}.scholarship_id", 'scholarship'),
                    'name' => $this->getMapValue("scholarships.{$index}.name"),
                    'amount' => $item['use_amount'] ? $amount : null,
                    'percentage' => $item['use_amount'] ? null : $percentage,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $applicableSubtotal = $this->rowSubtotal;

                if (
                    !empty($item['applies_to']) &&
                    count($item['applies_to']) !== $this->localInvoiceItemsKeyedById->count()
                ) {
                    // This is probably "bad practice" since the reduce's
                    // callback adds items to the pivot collection (side effect),
                    // but I don't want to iterate the items again
                    $applicableSubtotal = collect($item['applies_to'])
                        ->reduce(function (int $total, string $id) use ($attributes) {
                            $invoiceItem = $this->localInvoiceItemsKeyedById->get($id);

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
     * @return Collection
     */
    protected function buildPaymentSchedules(): Collection
    {
        return collect($this->getMapField('payment_schedules'))
            ->each(function (array $item, int $scheduleIndex) {
                $scheduleUuid = $this->uuid();
                $scheduleAttributes = [
                    'uuid' => $scheduleUuid,
                    'batch_id' => $this->batchId,
                    'invoice_uuid' => $this->rowInvoiceUuid,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                $scheduleAmount = collect($item['terms'])
                    ->reduce(function (
                        int $total,
                        array $term,
                        int $termIndex
                    ) use ($scheduleUuid, $scheduleIndex) {
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
                            : $this->rowPreTaxSubtotal * $percentage;

                        $this->localInvoicePaymentTerms->push([
                            'uuid' => $this->uuid(),
                            'batch_id' => $this->batchId,
                            'invoice_uuid' => $this->rowInvoiceUuid,
                            'invoice_payment_schedule_uuid' => $scheduleUuid,
                            'due_at' => $this->getMapValue(
                                "payment_schedules.{$scheduleIndex}.terms.{$termIndex}.due_at",
                                'date time'
                            ),
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

    /**
     * Builds the invoice tax items for the invoice if applicable,
     * and return the total tax due regardless of tax items
     *
     * @return int
     */
    protected function buildTaxItemAttributes(): int
    {
        // Not collecting tax means no tax due
        if (!$this->collectingTax()) {
            return 0;
        }

        $taxItems = $this->getMapValue(key: 'tax_items', default: []);

        // If we're applying to all items
        // or there is only one item,
        // apply the general tax rate
        if (
            $this->getMapValue(key: 'apply_tax_to_all_items', default: true) ||
            count($taxItems) < 2
        ) {
            return $this->rowPreTaxSubtotal * $this->getMapValue('tax_rate', 'tax rate');
        }

        return collect($taxItems)
            ->filter(fn ($taxItem) => $taxItem['selected'])
            ->reduce(function (int $total, array $taxItem, int $index) {
                $invoiceItem = $this->localInvoiceItemsKeyedById->get($taxItem['item_id']);
                $discount = $this->getItemDiscount($taxItem['item_id']);
                $taxRate = $this->getMapValue("tax_items.{$index}.tax_rate", 'percentage');
                $amount = round(($invoiceItem['amount'] - $discount) * $taxRate);

                $this->localInvoiceTaxItems->push([
                    'uuid' => $this->uuid(),
                    'invoice_uuid' => $this->rowInvoiceUuid,
                    'invoice_item_uuid' => $invoiceItem['uuid'],
                    'amount' => $amount,
                    'tax_rate' => $taxRate,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);

                return $total + $amount;
            }, 0);
    }

    /**
     * This gets the total discount for an individual item
     */
    protected function getItemDiscount(string|int $itemId): int
    {
        return collect($this->getMapField('scholarships'))
            ->reduce(function (int $total, array $item, int $index) use ($itemId) {
                $amount = $this->getMapValue("scholarships.{$index}.amount", 'currency');
                $percentage = $this->getMapValue("scholarships.{$index}.percentage", 'percentage');

                // Don't process if there isn't an amount
                // or percentage since scholarships are optional
                if (
                    ($item['use_amount'] && empty($amount)) ||
                    (!$item['use_amount'] && empty($percentage)) ||
                    (
                        !empty($item['applies_to']) &&
                        !in_array($itemId, $item['applies_to'])
                    )
                ) {
                    return $total;
                }

                $invoiceItem = $this->localInvoiceItemsKeyedById->get($itemId);
                $itemSubtotal = $invoiceItem['amount'];

                // Get the relative discount amount based on this item's
                // proportion of the subtotal
                $ratio = $this->rowSubtotal > 0
                    ? $itemSubtotal / $this->rowSubtotal
                    : 0;
                $discount = round($amount * $ratio);

                // If we're not using amount calculate the discount
                // based on the appropriate subtotal
                if (!$item['use_amount']) {
                    $discount = $itemSubtotal * $percentage;
                }

                // Don't let the discount exceed the subtotal
                if ($discount > $itemSubtotal) {
                    $discount = $itemSubtotal;
                }

                return $total + $discount;
            }, 0);
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

                $this->rowInvoiceUuid = $this->uuid();

                // Build the items
                $this->localInvoiceItemsKeyedById = $this->buildInvoiceItems();
                $this->rowSubtotal = $this->localInvoiceItemsKeyedById->reduce(fn ($total, $item) => $total + $item['amount']);
                $this->localInvoiceItems = $this->localInvoiceItemsKeyedById->values();

                // Build the scholarships
                $this->rowDiscountTotal = $this->buildInvoiceScholarships();

                // If the discount is greater than the row subtotal,
                // set the discount to be the subtotal's value.
                // Not sure if this is desired behavior, just my guess
                // and could change after review
                if ($this->rowDiscountTotal > $this->rowSubtotal) {
                    $this->rowDiscountTotal = $this->rowSubtotal;
                }

                // Set the pretax total, 0 if negative
                // Would we support negative account balance?
                $this->rowPreTaxSubtotal = $this->rowSubtotal - $this->rowDiscountTotal;

                // Build the payment schedules
                $this->buildPaymentSchedules();

                // Build the tax items and set the tax due
                $this->rowTaxDue = $this->buildTaxItemAttributes();

                // After everything, generate the invoice attributes and add them to the collection
                $invoiceAttributes = $this->getInvoiceAttributes();
                $validator = Validator::make($invoiceAttributes, Invoice::getValidationRules());

                // If the attributes fail validation,
                // don't commit anything and set the reasons
                if ($validator->fails()) {
                    $message = collect($validator->errors()->toArray())
                        ->reduce(function (string $message, $errors, $key) {
                            if (!empty($message)) {
                                $message .=', ';
                            }

                            $attribute = ucfirst($key);
                            $error = $errors[0];
                            $message .= "{$attribute}: {$error}";
                            return $message;
                        }, '');
                    $this->addResult($message, false);
                    $this->resetLocalStores();
                    return;
                }

                $this->localInvoices->push($this->getInvoiceAttributes());

                // Add the successful result
                $this->addResult($this->rowInvoiceUuid);

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
            'results' => $this->results->toArray(),
        ]);

        ray($this->import);

        // If we're just building models, only return the import
        // and the model's collection instead of storing the results
        if ($this->asModels) {
            return collect()->put('invoiceImport', $this->import)
                ->put('models', $this->models->keyBy('uuid'));
        }

        $storeResults = $this->store();
        $this->import->pdf_batch_id = $this->pdfBatchId;
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
            $invoice->setRelation(
                'invoiceTaxItems',
                $this->localInvoiceTaxItems->map(function ($item) {
                    $taxItem = new InvoiceTaxItem($item);
                    $invoiceItem = $this->localInvoiceItems->firstWhere('uuid', $taxItem->invoice_item_uuid);
                    $taxItem->setRelation('invoiceItem', new InvoiceItem($invoiceItem));

                    return $taxItem;
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
            $this->invoiceTaxItems = $this->invoiceTaxItems->merge($this->localInvoiceTaxItems);
        }

        $this->resetLocalStores();
    }
}
