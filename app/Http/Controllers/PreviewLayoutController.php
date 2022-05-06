<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLayout;
use Illuminate\Http\Request;

class PreviewLayoutController extends Controller
{
    public function __invoke(Request $request, InvoiceLayout $layout)
    {
        $this->authorize('view', $layout);

        /** @var Invoice $invoice */
        $invoice = $request->school()
            ->invoices()
            ->inRandomOrder()
            ->first();

        if (!$invoice) {
            session()->flash('error', __('No invoices exist to preview.'));
            return to_route('layouts.index');
        }

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
