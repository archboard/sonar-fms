<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\Scholarship;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Invoice::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $school = $this->school();

        return [
            'students' => [
                'required',
                'array',
                'min:1',
            ],
            'title' => ['required', 'max:255'],
            'description' => 'nullable',
            'due_at' => 'nullable|date',
            'invoice_date' => 'nullable|date',
            'available_at' => 'nullable|date',
            'term_id' => [
                'nullable',
                Rule::in($school->terms()->pluck('id')),
            ],
            'grade_level_adjustment' => ['nullable', 'integer'],
            'grade_level' => ['nullable', 'integer'],
            'notify' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.id' => ['string'],
            'items.*.fee_id' => [
                'nullable',
                Rule::in($school->fees()->pluck('id')),
            ],
            'items.*.name' => 'required',
            'items.*.amount_per_unit' => 'required|integer',
            'items.*.quantity' => 'required|integer',
            'scholarships' => 'array',
            'scholarships.*.scholarship_id' => [
                'nullable',
                Rule::in($school->scholarships()->pluck('id')),
            ],
            'scholarships.*.name' => 'required',
            'scholarships.*.amount' => 'nullable|integer|required_without:scholarships.*.percentage',
            'scholarships.*.percentage' => 'nullable|numeric|max:100|required_without:scholarships.*.amount',
            'scholarships.*.resolution_strategy' => [
                'nullable',
                'required_with:scholarships.*.amount,scholarships.*.percentage',
                Rule::in(array_keys(Scholarship::getResolutionStrategies())),
            ],
            'scholarships.*.applies_to' => 'array',
            'payment_schedules' => 'array',
            'payment_schedules.*.terms' => 'array',
            'payment_schedules.*.terms.*.amount' => 'required|integer',
            'payment_schedules.*.terms.*.due_at' => 'nullable|date',
            'apply_tax' => [
                Rule::requiredIf(fn () => $school->collect_tax),
                'boolean',
            ],
            'use_school_tax_defaults' => [
                Rule::requiredIf(fn () => $school->collect_tax && $this->boolean('apply_tax')),
                'boolean',
            ],
            'tax_rate' => [
                Rule::requiredIf(fn () => $school->collect_tax &&
                    $this->boolean('apply_tax') &&
                    ! $this->boolean('use_school_tax_defaults')
                ),
                'nullable',
                'numeric',
            ],
            'tax_label' => [
                Rule::requiredIf(fn () => $school->collect_tax &&
                    $this->boolean('apply_tax') &&
                    ! $this->boolean('use_school_tax_defaults')
                ),
                'nullable',
            ],
            'apply_tax_to_all_items' => [
                Rule::requiredIf(fn () => $school->collect_tax &&
                    $this->boolean('apply_tax')
                ),
                'boolean',
            ],
            'tax_items' => [
                Rule::requiredIf(fn () => $school->collect_tax &&
                    $this->boolean('apply_tax') &&
                    ! $this->boolean('apply_tax_to_all_items')
                ),
                'array',
            ],
            'tax_items.*.item_id' => 'required|in_array:items.*.id',
            'tax_items.*.selected' => 'required|boolean',
            'tax_items.*.tax_rate' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'students.*' => __('You must create an invoice for at least one student.'),
            'scholarships.*.amount.required_without' => __('The amount is required when not using a percentage.'),
            'scholarships.*.percentage.required_without' => __('The percentage is required when not using an amount.'),
        ];
    }

    public function attributes()
    {
        return [];
    }
}
