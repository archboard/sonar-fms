<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoicePaymentResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class FetchInvoicePaymentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice payments', $invoice);

        $payments = $invoice->invoicePayments()
            ->with(
                'currency',
                'recordedBy',
            )
            ->orderBy('paid_at', 'desc')
            ->get();

        return InvoicePaymentResource::collection($payments);
    }
}
