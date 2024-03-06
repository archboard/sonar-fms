<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class MapInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(InvoiceImport $import)
    {
        $this->authorize('create', InvoiceImport::class);
        $title = __('Map Import');
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
                'label' => __('Column mapping'),
                'route' => route('invoices.imports.map', $import),
            ],
        ];
        $import->load('user');

        return inertia('invoices/imports/Map', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoiceImport' => $import->toResource(),
            'headers' => $import->headers,
            'errors' => $import->getMappingValidationErrors(),
        ])->withViewData(compact('title'));
    }

    public function update(Request $request, InvoiceImport $import)
    {
        $this->authorize('update', InvoiceImport::class);

        $import->mapping = $request->all();
        // Cache this value so we don't have to read the file each time
        $import->mapping_valid = $import->hasValidMapping();
        $import->save();

        session()->flash('success', __('Mapping saved successfully.'));

        return redirect()->route('invoices.imports.show', $import);
    }
}
