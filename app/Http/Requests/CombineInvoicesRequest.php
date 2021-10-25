<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CombineInvoicesRequest extends FormRequest
{
    public string $invoiceUuid;

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
            'draft' => 'nullable|boolean',
            'users' => 'required|array|min:1|exists:users,uuid',
            'title' => 'required',
            'description' => 'nullable',
            'due_at' => 'nullable|date',
            'invoice_date' => 'nullable|date',
            'available_at' => 'nullable|date',
            'term_id' => [
                'nullable',
                Rule::in($school->terms()->pluck('id')),
            ],
            'notify' => 'boolean',
            'payment_schedules' => 'array',
            'payment_schedules.*.terms' => 'array',
            'payment_schedules.*.terms.*.amount' => 'required|integer',
            'payment_schedules.*.terms.*.due_at' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'users.required' => __('Please choose at least one user.'),
            'users.min' => __('Please choose at least one user.'),
        ];
    }

    public function attributes()
    {
        return [];
    }
}
