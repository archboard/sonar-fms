<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class DuplicateInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('create', Invoice::class);

        $invoice->fullLoad();

        $title = __('Duplicate invoice :invoice_number for :student', [
            'invoice_number' => $invoice->number_formatted,
            'student' => $invoice->student->full_name,
        ]);
        $breadcrumbs = [
            [
                'label' => __('Students'),
                'route' => route('students.index'),
            ],
            [
                'label' => $invoice->student->full_name,
                'route' => route('students.show', $invoice->student),
            ],
            [
                'label' => __('Duplicate invoice :invoice_number', ['invoice_number' => $invoice->number_formatted]),
                'route' => route('invoices.duplicate', $invoice),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'student' => $invoice->student->toResource(),
            'defaultTemplate' => $invoice->asInvoiceTemplate(),
            'duplicating' => true,
        ])->withViewData(compact('title'));
    }
}
