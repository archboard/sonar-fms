<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentImportResource;
use App\Models\PaymentImport;
use App\Models\School;
use Illuminate\Http\Request;

class PaymentImportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PaymentImport::class, 'import');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request, School $school)
    {
        $title = __('Payment imports');
        $imports = $school->paymentImports()
            ->orderBy('created_at', 'desc')
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('payments/imports/Index', [
            'title' => $title,
            'imports' => PaymentImportResource::collection($imports),
            'permissions' => $request->user()->getPermissions(PaymentImport::class),
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create()
    {
        $title = __('Add a payment import');
        $breadcrumbs = [
            [
                'label' => __('Payments'),
                'route' => route('payments.index'),
            ],
            [
                'label' => __('Payment imports'),
                'route' => route('payments.imports.index'),
            ],
            [
                'label' => __('Create import'),
                'route' => route('payments.imports.create'),
            ],
        ];

        // Since the InvoiceImport and PaymentImport models
        // are essentially the same, reuse the same form
        return inertia('invoices/imports/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'method' => 'post',
            'endpoint' => route('payments.imports.store'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentImport $paymentImport)
    {
        //
    }
}
