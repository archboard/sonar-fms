<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidImportFileTypeException;
use App\Http\Requests\CreateFileImportRequest;
use App\Http\Requests\UpdateFileImportRequest;
use App\Http\Resources\PaymentImportResource;
use App\Models\InvoicePayment;
use App\Models\PaymentImport;
use App\Models\School;
use App\Models\User;
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
        $title = __('Payment Imports');
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
        $title = __('Add a Payment Import');
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
            'extensions' => ['csv', 'xlsx', 'xls'],
            'breadcrumbs' => $breadcrumbs,
            'method' => 'post',
            'endpoint' => route('payments.imports.store'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateFileImportRequest $request
     * @param School $school
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(CreateFileImportRequest $request, School $school)
    {
        /** @var PaymentImport $import */
        $import = $school->paymentImports()
            ->make(['tenant_id' => $school->tenant_id]);

        try {
            $import->createFromRequest($request);
        } catch (InvalidImportFileTypeException) {
            return back();
        }

        session()->flash('success', __('Payment import created successfully.'));

        return redirect()->route('payments.imports.map', $import);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentImport  $import
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, PaymentImport $import)
    {
        $title = __('Import details for :filename', [
            'filename' => $import->file_name,
        ]);
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
                'label' => $import->file_name,
                'route' => route('payments.imports.show', $import),
            ],
        ];
        /** @var User $user */
        $user = $request->user();
        $results = collect();

        if ($request->has('preview')) {
            $results = $import->importAsModels();
        }

        return inertia('payments/imports/Show', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'paymentImport' => $results->get('paymentImport', $import->load('user'))
                ->toResource(),
            'results' => $import->results ?? [],
            'previewResults' => $results->get('models', []),
            'permissions' => [
                'imports' => $user->getPermissions(PaymentImport::class),
                'payments' => $user->getPermissions(InvoicePayment::class),
            ],
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentImport  $import
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(PaymentImport $import)
    {
        if ($import->imported_records > 0) {
            session()->flash('error', __('You have already imported the payments. Please create a new import.'));
            return redirect()->route('payments.imports.show', $import);
        }

        $title = __('Edit Payment Import');
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
                'label' => $import->file_name,
                'route' => route('payments.imports.show', $import),
            ],
            [
                'label' => __('Edit import'),
                'route' => route('payments.imports.edit', $import),
            ],
        ];

        return inertia('invoices/imports/Create', [
            'title' => $title,
            'extensions' => ['csv', 'xlsx', 'xls'],
            'breadcrumbs' => $breadcrumbs,
            'existingImport' => $import->toResource(),
            'method' => 'put',
            'endpoint' => route('payments.imports.update', $import),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateFileImportRequest $request
     * @param \App\Models\PaymentImport $import
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateFileImportRequest $request, PaymentImport $import)
    {
        try {
            $import->updateFromRequest($request);
        } catch (InvalidImportFileTypeException) {
            return back();
        }

        if ($import->mapping_valid) {
            return redirect()->route('payments.imports.show', $import);
        }

        return redirect()->route('payments.imports.map', $import);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentImport  $import
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentImport $import)
    {
        //
    }
}
