<?php

namespace App\Http\Controllers;

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
}
