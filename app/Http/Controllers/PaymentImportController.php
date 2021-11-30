<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentImportResource;
use App\Models\PaymentImport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

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
            'breadcrumbs' => $breadcrumbs,
            'method' => 'post',
            'endpoint' => route('payments.imports.store'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            'files' => 'array|required',
            'files.*.file' => 'file',
            'heading_row' => 'required|integer',
            'starting_row' => 'required|integer',
        ]);

        $fileData = Arr::first($data['files']);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $fileData['file'];

        /** @var PaymentImport $import */
        $import = $school->paymentImports()
            ->make([
                'tenant_id' => $school->tenant_id,
                'user_uuid' => $request->user()->id,
                'file_path' => PaymentImport::storeFile($uploadedFile, $school),
                'heading_row' => $data['heading_row'],
                'starting_row' => $data['starting_row'],
            ]);

        try {
            $import->setTotalRecords()
                ->save();
        } catch (\ValueError $exception) {
            session()->flash('error', __('There was a problem reading the file. Please make sure it is not password protected and try again.'));
            return back();
        }

        session()->flash('success', __('Payment import created successfully.'));

        return redirect()->route('payments.imports.map', $import);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentImport  $import
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentImport $import)
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentImport  $import
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentImport $import)
    {
        //
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
