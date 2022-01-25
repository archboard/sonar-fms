<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoicePaymentResource;
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
    public function __invoke(Request $request, string $invoice)
    {
        $this->authorize('viewAny', InvoicePayment::class);

        $payments = InvoicePayment::forInvoice($invoice)
            ->with(
                'currency',
                'recordedBy',
            )
            ->orderBy('paid_at', 'desc')
            ->get();

        return InvoicePaymentResource::collection($payments);
    }
}
