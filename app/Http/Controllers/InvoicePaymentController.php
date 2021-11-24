<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoicePaymentRequest;
use App\Http\Resources\InvoicePaymentResource;
use App\Http\Resources\PaymentMethodDriverResource;
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
        $payments = $request->school()
            ->invoicePayments()
            ->filter($request->all())
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
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create(Request $request)
    {
        $title = __('Record payment');
        $paymentMethods = $request->school()->getPaymentMethods();
        $breadcrumbs = [
            [
                'label' => __('Payments'),
                'route' => route('payments.index'),
            ],
            [
                'label' => __('Record payment'),
                'route' => route('payments.create'),
            ],
        ];
        $invoice = $request->has('invoice_uuid')
            ? Invoice::where('uuid', $request->get('invoice_uuid'))
                ->with(
                    'student',
                    'students',
                    'parent',
                    'children',
                    'currency',
                    'invoicePaymentSchedules.invoicePaymentTerms'
                )
                ->first()
            : new Invoice;
        $paidBy = $request->has('user_id')
            ? User::find($request->input('user_id'))
            : new User;

        return inertia('payments/Create', [
            'title' => $title,
            'paymentMethods' => PaymentMethodDriverResource::collection($paymentMethods),
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $invoice->toResource(),
            'paidBy' => $paidBy->toResource(),
            'term' => $request->input('term'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoicePaymentRequest $request)
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->tenant()->id;
        $data['school_id'] = $request->school()->id;
        $data['recorded_by'] = $request->id();

        $payment = InvoicePayment::create($data);
        $payment->invoice->recordPayment($payment);

        session()->flash('success', __('Payment recorded successfully.'));

        return redirect()->route('invoices.show', $payment->invoice);
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
