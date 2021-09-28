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
                'label' => $invoice->title,
                'route' => route('students.invoices.show', [$invoice->student, $invoice]),
            ],
            [
                'label' => __('Duplicate'),
                'route' => route('invoices.duplicate', $invoice),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'students' => [$invoice->student_id],
            'defaultTemplate' => $invoice->asInvoiceTemplate(),
            'duplicating' => true,
            'method' => 'post',
            'endpoint' => route('invoices.store'),
        ])->withViewData(compact('title'));
    }
}
