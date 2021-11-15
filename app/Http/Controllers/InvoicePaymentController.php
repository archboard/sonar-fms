<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoicePaymentResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class InvoicePaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoicePayment::class, 'payment');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $title = __('Payments');
        $payments = InvoicePayment::filter($request->all())
            ->with('invoice', 'currency')
            ->paginate($request->input('perPage', 25));

        return inertia('payments/Index', [
            'title' => $title,
            'payments' => InvoicePaymentResource::collection($payments),
            'permissions' => [
                'invoices' => [
                    'viewAny' => $user->can('viewAny', Invoice::class),
                ],
                'students' => [
                    'viewAny' => $user->can('viewAny', Student::class),
                ],
                'payments' => $user->getPermissions(Student::class),
            ],
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Invoice $invoice)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoicePayment  $invoicePayment
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice, InvoicePayment $invoicePayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoicePayment  $invoicePayment
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice, InvoicePayment $invoicePayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoicePayment  $invoicePayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice, InvoicePayment $invoicePayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoicePayment  $invoicePayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice, InvoicePayment $invoicePayment)
    {
        //
    }
}
