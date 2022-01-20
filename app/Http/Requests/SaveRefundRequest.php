<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\InvoiceRefund;
use Illuminate\Foundation\Http\FormRequest;

class SaveRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', InvoiceRefund::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var Invoice $invoice */
        $invoice = $this->route('invoice');

        return [
            'amount' => ['integer', 'min:1', "max:{$invoice->total_paid}"],
            'transaction_details' => ['nullable', 'max:255'],
            'notes' => ['nullable'],
        ];
    }
}
