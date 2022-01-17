<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceRefund;
use Illuminate\Http\Request;

class InvoiceRefundController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceRefund::class, 'refund');
    }

    public function index(Request $request)
    {

    }

    public function create(Invoice $invoice)
    {
        if ($invoice->invoicePayments->isEmpty()) {
            session()->flash('error', __('No payments have been made yet.'));
            return redirect()->route('invoices.show', $invoice);
        }

        $title = __('Refund for :invoice_number', [
            'invoice_number' => $invoice->invoice_number,
        ]);
        $invoice->load(
            'invoiceRefunds',
        );

        return inertia('refunds/Create', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
        ])->withViewData(compact('title'));
    }
}
