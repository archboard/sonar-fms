<?php

namespace App\Http\Requests;

use App\Models\InvoicePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', InvoicePayment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $school = $this->school();
        $payment = $this->route('payment');

        return [
            'payment_method_id' => [
                'nullable',
                Rule::exists('payment_methods', 'id')
                    ->where('school_id', $school->id),
            ],
            'paid_at' => ['required', 'date'],
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($payment->invoice->remaining_balance + $payment->amount), // Factor in the original payment
            ],
            'made_by' => [
                'nullable',
                Rule::exists('users', 'uuid')
                    ->where('tenant_id', $payment->tenant_id),
            ],
            'invoice_payment_term_uuid' => [
                'nullable',
                Rule::exists('invoice_payment_terms', 'uuid')
                    ->where('invoice_uuid', $payment->invoice_uuid),
            ],
            'transaction_details' => 'nullable',
            'notes' => 'nullable',
        ];
    }
}
