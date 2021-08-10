<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoiceForStudentsRequest;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
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

        /** @var User $user */
        $user = $request->user();

        return inertia('invoices/Index', [
            'title' => $title,
            'invoices' => InvoiceResource::collection($invoices),
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
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
     * @param CreateInvoiceForStudentsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        return redirect()->route('invoices.index', ['batch_id' => '123']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, Invoice $invoice)
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

        /** @var User $user */
        $user = $request->user();

        return inertia('invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'student' => $invoice->student->toResource(),
            'breadcrumbs' => $breadcrumbs,
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
            ],
        ])->withViewData(compact('title'));
    }

    /**
     * @param Request $request
     * @param Invoice $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(Request $request, Invoice $invoice)
    {
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
            'student' => $invoice->student->toResource(),
        ])->withViewData(compact('title'));
    }
}
