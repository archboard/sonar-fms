<?php

namespace App\Http\Requests;

use App\Models\Invoice;
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
        ];
    }
}
