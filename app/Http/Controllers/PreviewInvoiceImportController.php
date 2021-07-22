<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class PreviewInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('create', $import);

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
        $results = $import->importAsModels();

        return inertia('invoices/imports/Show', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoiceImport' => $results->get('invoiceImport')->toResource(),
            'results' => $import->results ?? [],
            'errors' => $import->getMappingValidationErrors(),
            'permissions' => $request->user()->getPermissions(InvoiceImport::class),
            'previewResults' => $results->get('models'),
            'isPreview' => true,
        ])->withViewData(compact('title'));
    }
}
