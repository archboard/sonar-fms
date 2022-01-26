<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceRefund;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
            ->notAChild()
            ->with([
                'student',
                'students',
                'school',
                'currency',
            ])
            ->withCount('children')
            ->filter($request->all())
            ->paginate($request->input('perPage', 25));

        /** @var User $user */
        $user = $request->user();
        $user->load('invoiceSelections');

        return inertia('invoices/Index', [
            'title' => $title,
            'invoices' => InvoiceResource::collection($invoices),
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
                'payments' => [
                    'create' => $user->can('create', InvoicePayment::class),
                ],
                'refunds' => [
                    'create' => $user->can('create', InvoiceRefund::class),
                ],
            ],
        ])->withViewData(compact('title'));
    }

    /**
     * Shows the form for creating a new invoice
     * where students are added
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create()
    {
        $title = __('Create a new invoice');
        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => __('New invoice'),
                'route' => route('invoices.create'),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'students' => [],
            'endpoint' => route('invoices.store'),
            'method' => 'post',
        ])->withViewData(compact('title'));
    }

    /**
     * @param CreateInvoiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        $results = InvoiceFromRequestFactory::make($request)
            ->build();

        return Invoice::successfullyCreatedResponse($results);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param \App\Models\Invoice $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, Invoice $invoice)
    {
        $title = $invoice->title . ': ' . $invoice->invoice_number;
        $invoice->load([
            'student',
            'students',
            'school',
            'currency',
            'invoiceItems.invoice.currency',
            'invoiceScholarships.invoice.currency',
            'invoicePaymentSchedule',
            'invoicePaymentSchedules',
            'invoicePaymentSchedules.invoicePaymentTerms',
            'children',
            'parent',
            'parent.invoicePaymentSchedules.invoicePaymentTerms',
            'activities',
            'activities.causer',
        ])
        ->loadChildren();

        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => $invoice->invoice_number,
                'route' => route('invoices.show', $invoice),
            ]
        ];

        /** @var User $user */
        $user = $request->user();

        return inertia('invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'breadcrumbs' => $breadcrumbs,
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
                'payments' => [
                    'create' => $user->can('create', InvoicePayment::class),
                    'viewAny' => $user->can('viewAny', InvoicePayment::class),
                ],
                'refunds' => [
                    'create' => $user->can('create', InvoiceRefund::class),
                    'viewAny' => $user->can('viewAny', InvoiceRefund::class),
                ],
            ],
        ])->withViewData(compact('title'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->children()->exists()) {
            return redirect("/combine/{$invoice->uuid}");
        }

        $title = __('Update invoice');
        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => $invoice->title,
                'route' => route('invoices.show', $invoice),
            ],
            [
                'label' => __('Edit'),
                'route' => route('invoices.edit', $invoice),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $invoice->forEditing(),
            'method' => 'put',
            'endpoint' => route('invoices.update', $invoice),
            'allowStudentEditing' => false,
        ])->withViewData(compact('title'));
    }

    /**
     * This creates an invoice from a draft,
     * but just deletes the original invoice
     * after creating a new invoice from the request
     *
     * @param UpdateInvoiceRequest $request
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $results = InvoiceFromRequestFactory::make($request)
            ->build();

        // Scrap the original invoice in favor of the created one
        $invoice->delete();

        return Invoice::successfullyCreatedResponse($results);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        session()->flash('success', __('Invoice deleted successfully.'));

        return redirect()->route('invoices.index');
    }
}
