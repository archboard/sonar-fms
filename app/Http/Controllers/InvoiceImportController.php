<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceImportResource;
use App\Models\InvoiceImport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        ])->withViewData(compact('title'));
    }

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

        /** @var InvoiceImport $import */
        $import = $school->invoiceImports()
            ->make([
                'user_id' => $request->user()->id,
                'file_path' => InvoiceImport::storeFile($uploadedFile, $school),
                'heading_row' => $data['heading_row'],
                'starting_row' => $data['starting_row'],
            ]);

        $import->setTotalRecords()
            ->save();

        session()->flash('success', __('Invoice import created successfully.'));

        return redirect()->route('invoices.imports.map', $import);
    }

    public function show(InvoiceImport $import)
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
        ])->withViewData(compact('title'));
    }

    public function edit(InvoiceImport $import)
    {
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
            'invoiceImport' => $import->toResource(),
        ])->withViewData(compact('title'));
    }

    public function update(Request $request, InvoiceImport $import)
    {
        $data = $request->validate([
            'files' => 'array|required',
            'heading_row' => 'required|integer',
            'starting_row' => 'required|integer',
        ]);

        $fileData = Arr::first($data['files']);
        $import->fill(Arr::except($data, 'files'));

        // This key only exists if the file hasn't been changed
        if (!isset($fileData['existing'])) {
            /** @var UploadedFile $file */
            $file = $fileData['file'];

            if (!$file->isValid()) {
                session()->flash('error', __('Invalid file.'));

                return back();
            }

            Storage::delete($import->file_path);
            Storage::deleteDirectory(dirname($import->file_path));
            $import->file_path = InvoiceImport::storeFile($file, $request->school());
            $import->mapping_valid = $import->hasValidMapping();
            $import->setTotalRecords();
        }

        $import->save();

        session()->flash('success', __('Import updated successfully.'));

        if ($import->mapping_valid) {
            return redirect()->route('invoices.imports.show', $import);
        }

        return redirect()->route('invoices.imports.map', $import);
    }
}
