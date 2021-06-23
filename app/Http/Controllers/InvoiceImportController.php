<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceImportResource;
use App\Models\InvoiceImport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
        $title = __('Import Invoices');
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
        ]);

        $fileData = Arr::first($data['files']);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $fileData['file'];
        $now = now()->format('U') . '-' . Str::random(8);

        /** @var InvoiceImport $import */
        $import = $school->invoiceImports()
            ->create([
                'user_id' => $request->user()->id,
                'file_path' => $uploadedFile->storeAs(
                    "imports/{$school->id}/{$now}",
                    $uploadedFile->getClientOriginalName()
                ),
            ]);
        ray($import);

        session()->flash('success', __('Invoice import created successfully.'));

        return redirect()->route('invoices.imports.show', $import);
    }
}
