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
            'title' => 'required',
            'description' => 'nullable',
            'due_at' => 'nullable|date',
            'term_id' => [
                'nullable',
                Rule::in($school->terms->pluck('id')),
            ],
            'notify' => 'boolean',
            'items' => 'array|min:1',
            'items.*.fee_id' => [
                'nullable',
                Rule::in($school->fees->pluck('id')),
            ],
            'items.*.sync_with_fee' => 'required|boolean',
            'items.*.name' => 'required',
            'items.*.amount_per_unit' => 'required|integer',
            'items.*.quantity' => 'required|integer',
            'scholarships' => 'array',
            'scholarships.*.scholarship_id' => [
                'nullable',
                Rule::in($school->scholarships->pluck('id')),
            ],
            'scholarships.*.sync_with_scholarship' => 'required|boolean',
            'scholarships.*.name' => 'required',
            'scholarships.*.amount' => 'nullable|integer|required_without:scholarships.*.percentage',
            'scholarships.*.percentage' => 'nullable|numeric|max:100|required_without:scholarships.*.amount',
            'scholarships.*.resolution_strategy' => [
                'nullable',
                'required_with:scholarships.*.amount,scholarships.*.percentage',
                Rule::in(array_keys(Scholarship::getResolutionStrategies())),
            ],
        ];
    }

    public function messages()
    {
        return [
            'scholarships.*.amount.required_without' => __('The amount is required when not using a percentage.'),
            'scholarships.*.percentage.required_without' => __('The percentage is required when not using an amount.'),
        ];
    }

    public function attributes()
    {
        return [];
    }
}
