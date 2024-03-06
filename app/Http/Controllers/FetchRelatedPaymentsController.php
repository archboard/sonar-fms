<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoicePaymentResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FetchRelatedPaymentsController extends Controller
{
    /**
     * Gets payments of child invoices that aren't child
     * payments either
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice payments', $invoice);

        $payments = InvoicePayment::whereNull('invoice_payments.parent_uuid')
            ->whereHas('invoice', function (Builder $builder) use ($invoice) {
                $builder->where('invoices.parent_uuid', $invoice->uuid);
            })
            ->with([
                'currency',
                'recordedBy',
                'invoice',
            ])
            ->orderBy('paid_at', 'desc')
            ->get();

        return InvoicePaymentResource::collection($payments);
    }
}
