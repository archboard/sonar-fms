<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoicePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\InvoicePaymentResource;
use App\Http\Resources\PaymentMethodDriverResource;
use App\Http\Resources\UserResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
     */
    public function create(Request $request)
    {
        $title = __('Record payment');
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Payments'),  route('payments.index')),
            $this->makeBreadcrumb(__('Record payment'),  route('payments.create')),
        ];
        $invoice = $request->has('invoice_uuid')
            ? Invoice::where('uuid', $request->get('invoice_uuid'))
                ->with(
                    'student',
                    'students',
                    'parent',
                    'children',
                    'children.student',
                    'currency',
                    'invoicePaymentSchedules.invoicePaymentTerms'
                )
                ->first()
            : new Invoice;

        if ($invoice->voided_at) {
            session()->flash('error', __('Invoice has been voided.'));
            return redirect()->route('invoices.show', $invoice);
        }

        if ($invoice->uuid) {
            $breadcrumbs = [
                $this->makeBreadcrumb(__('Invoices'), route('invoices.index')),
                $this->makeBreadcrumb($invoice->invoice_number, route('invoices.show', $invoice)),
                $this->makeBreadcrumb(__('Record payment'), route('payments.create', ['invoice_uuid' => $invoice->uuid])),
            ];
        }

        $paidBy = $request->has('user_id')
            ? User::find($request->input('user_id'))
            : new User;

        return inertia('payments/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $invoice->toResource(),
            'paidBy' => $paidBy->toResource(),
            'term' => $request->input('term'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateInvoicePaymentRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoicePaymentRequest $request)
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->tenant()->id;
        $data['school_id'] = $request->school()->id;
        $data['recorded_by'] = $request->id();
        $data['original_amount'] = $data['amount'];

        $payment = InvoicePayment::create($data);
        $payment->invoice->recordPayment($payment);

        session()->flash('success', __('Payment recorded successfully.'));

        return redirect()->route('invoices.show', $payment->invoice);
    }

    /**
     * Display the specified resource.
     *
     * @param InvoicePayment $payment
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(InvoicePayment $payment)
    {
        return $payment
            ->fullLoad()
            ->load('activities')
            ->load('activities.causer')
            ->toResource();
    }

    public function edit(InvoicePayment $payment)
    {
        $title = __('Edit payment for :invoice_number', [
            'invoice_number' => $payment->invoice->invoice_number,
        ]);
        $breadcrumbs = [
            $this->makeBreadcrumb($payment->invoice->invoice_number, route('invoices.show', $payment->invoice)),
            $this->makeBreadcrumb(__('Edit payment'), route('payments.edit', $payment)),
        ];
        $paidBy = $payment->madeBy ?? new User;

        return inertia('payments/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $payment->invoice->toResource(),
            'payment' => $payment->forEdit(),
            'method' => 'put',
            'endpoint' => route('payments.update', $payment),
            'paidBy' => $paidBy->toResource(),
        ])->withViewData(compact('title'));
    }

    public function update(UpdatePaymentRequest $request, InvoicePayment $payment)
    {
        $payment->updateFromRequest($request);

        session()->flash('success', __('Payment updated successfully.'));

        return redirect()->route('invoices.show', $payment->invoice_uuid);
    }
}
