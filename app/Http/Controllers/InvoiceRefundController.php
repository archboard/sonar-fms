<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveRefundRequest;
use App\Http\Resources\InvoiceRefundResource;
use App\Models\Invoice;
use App\Models\InvoiceRefund;
use Illuminate\Http\Request;

class InvoiceRefundController extends Controller
{
    public function index(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice refunds', $invoice);

        $refunds = $invoice->invoiceRefunds()
            ->orderBy('refunded_at', 'desc')
            ->with('user', 'currency')
            ->get();

        return InvoiceRefundResource::collection($refunds);
    }

    public function create(Invoice $invoice)
    {
        $this->authorize('create', InvoiceRefund::class);

        if ($invoice->invoicePayments->isEmpty()) {
            session()->flash('error', __('No payments have been made yet.'));

            return redirect()->route('invoices.show', $invoice);
        }

        $title = __('Refund for :invoice_number', [
            'invoice_number' => $invoice->invoice_number,
        ]);
        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => $invoice->invoice_number,
                'route' => route('invoices.show', $invoice),
            ],
            [
                'label' => __('Record refund'),
                'route' => route('invoices.refunds.create', $invoice),
            ],
        ];
        $invoice->load(
            'parent',
            'currency',
            'invoiceRefunds',
        );

        return inertia('refunds/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $invoice->toResource(),
        ])->withViewData(compact('title'));
    }

    public function store(SaveRefundRequest $request, Invoice $invoice)
    {
        $this->authorize('create', InvoiceRefund::class);

        $invoice->recordRefund($request);

        session()->flash('success', __('Refund saved successfully.'));

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(string $invoice, InvoiceRefund $refund)
    {
        $this->authorize('view invoice refund', $refund);

        $refund->load(
            'currency',
            'invoice',
            'invoice.currency',
            'invoice.student',
            'invoice.students',
            'user',
        );

        return $refund->toResource();
    }
}
