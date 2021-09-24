<?php

namespace App\Http\Requests;

use App\Models\Invoice;

class UpdateInvoiceRequest extends CreateInvoiceRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', Invoice::class);
    }
}
