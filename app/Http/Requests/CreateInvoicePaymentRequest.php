<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvoicePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', InvoicePayment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $invoice = Invoice::find($this->input('invoice_uuid'));

        return [
            'invoice_uuid' => [
                'required',
                Rule::exists('invoices', 'uuid')
                    ->where('school_id', $this->school()->id),
            ],
            'payment_method_id' => [
                'nullable',
                Rule::exists('payment_methods', 'id')
                    ->where('school_id', $this->school()->id),
            ],
            'paid_at' => ['required', 'date'],
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:' . $invoice->remaining_balance,
            ],
            'made_by' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('tenant_id', $this->tenant()->id),
            ],
            'invoice_payment_term_uuid' => [
                'nullable',
                Rule::exists('invoice_payment_terms', 'uuid')
                    ->where('invoice_uuid', $this->input('invoice_uuid')),
            ],
        ];
    }
}
