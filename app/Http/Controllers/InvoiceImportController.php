<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidImportFileTypeException;
use App\Http\Requests\CreateFileImportRequest;
use App\Http\Requests\UpdateFileImportRequest;
use App\Http\Resources\InvoiceImportResource;
use App\Models\InvoiceImport;
use App\Models\School;
use Illuminate\Http\Request;

class InvoiceImportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceImport::class, 'import');
    }

    public function index(Request $request, School $school)
    {
        $title = __('Invoice Imports');
        $imports = $school->invoiceImports()
            ->orderBy('created_at', 'desc')
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('invoices/imports/Index', [
            'title' => $title,
            'imports' => InvoiceImportResource::collection($imports),
            'permissions' => $request->user()->getPermissions(InvoiceImport::class),
        ])->withViewData(compact('title'));
    }

    public function create()
    {
        $title = __('Create an Import');
        $breadcrumbs = [
            [
                'label' => __('Invoice imports'),
                'route' => route('invoices.imports.index'),
            ],
            [
                'label' => __('Create an import'),
                'route' => route('invoices.imports.create'),
            ],
        ];

        return inertia('invoices/imports/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'extensions' => ['csv', 'xlsx', 'xls'],
            'endpoint' => route('invoices.imports.store'),
            'method' => 'post',
        ])->withViewData(compact('title'));
    }

    public function store(CreateFileImportRequest $request, School $school)
    {
        /** @var InvoiceImport $import */
        $import = $school->invoiceImports()
            ->make();

        try {
            $import->createFromRequest($request);
        } catch (InvalidImportFileTypeException) {
            return back();
        }

        session()->flash('success', __('Invoice import created successfully.'));

        return redirect()->route('invoices.imports.map', $import);
    }

    public function show(Request $request, InvoiceImport $import)
    {
        $import->load('user');
        $title = __('Import details for :filename', [
            'filename' => $import->file_name,
        ]);
        $breadcrumbs = [
            [
                'label' => __('Invoice imports'),
                'route' => route('invoices.imports.index'),
            ],
            [
                'label' => $import->file_name,
                'route' => route('invoices.imports.show', $import),
            ],
        ];

        return inertia('invoices/imports/Show', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoiceImport' => $import->toResource(),
            'results' => $import->results ?? [],
            'permissions' => $request->user()->getPermissions(InvoiceImport::class),
        ])->withViewData(compact('title'));
    }

    public function edit(InvoiceImport $import)
    {
        if ($import->imported_records > 0) {
            session()->flash('error', __('You have already imported records for this invoice. Please create a new import.'));

            return redirect()->route('invoices.imports.show', $import);
        }

        $title = __('Edit Import');
        $breadcrumbs = [
            [
                'label' => __('Invoice imports'),
                'route' => route('invoices.imports.index'),
            ],
            [
                'label' => $import->file_name,
                'route' => route('invoices.imports.show', $import),
            ],
            [
                'label' => __('Edit import'),
                'route' => route('invoices.imports.edit', $import),
            ],
        ];

        return inertia('invoices/imports/Create', [
            'title' => $title,
            'extensions' => ['csv', 'xlsx', 'xls'],
            'breadcrumbs' => $breadcrumbs,
            'existingImport' => $import->toResource(),
            'method' => 'put',
            'endpoint' => route('invoices.imports.update', $import),
        ])->withViewData(compact('title'));
    }

    public function update(UpdateFileImportRequest $request, InvoiceImport $import)
    {
        try {
            $import->updateFromRequest($request);
        } catch (InvalidImportFileTypeException) {
            return back();
        }

        if ($import->mapping_valid) {
            return redirect()->route('invoices.imports.show', $import);
        }

        return redirect()->route('invoices.imports.map', $import);
    }
}
