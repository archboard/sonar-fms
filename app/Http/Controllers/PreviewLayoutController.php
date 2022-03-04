<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLayout;
use Illuminate\Http\Request;

class PreviewLayoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, InvoiceLayout $layout)
    {
        $this->authorize('view', $layout);

        /** @var Invoice $invoice */
        $invoice = $request->school()
            ->invoices()
            ->inRandomOrder()
            ->first();

        $invoice->load([
            'invoiceScholarships.appliesTo',
            'invoicePaymentSchedules.invoicePaymentTerms',
        ]);
        $layout = $request->school()->getDefaultInvoiceLayout();

        $title = __('Invoice #:number', ['number' => $invoice->id]);

        return view('invoice', [
            'layout' => $layout,
            'invoice' => $invoice,
            'title' => $title,
            'currency' => $invoice->currency,
        ]);
    }
}
