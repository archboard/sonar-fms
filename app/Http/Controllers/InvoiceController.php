<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Invoices');
        $invoices = $request->school()
            ->invoices()
            ->with([
                'student',
                'school',
                'currency',
            ])
            ->filter($request->all())
            ->paginate($request->input('perPage', 25));

        return inertia('invoices/Index', [
            'title' => $title,
            'invoices' => InvoiceResource::collection($invoices),
        ])->withViewData(compact('title'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Invoice $invoice)
    {
        $title = $invoice->title;
        $invoice->fullLoad();

        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => back()->getTargetUrl(),
            ],
            [
                'label' => __('Invoice #:invoice_number', [
                    'invoice_number' => $invoice->id,
                ]),
                'route' => route('invoices.show', $invoice),
            ]
        ];

        return inertia('invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'student' => $invoice->student->toResource(),
            'breadcrumbs' => $breadcrumbs,
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
