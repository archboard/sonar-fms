<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class MapInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param InvoiceImport $import
     * @return \Inertia\Response|\Inertia\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(InvoiceImport $import)
    {
        $this->authorize('create', InvoiceImport::class);
        $title = __('Map Import');
        $import->load('user');

        return inertia('invoices/imports/Map', [
            'title' => $title,
            'invoiceImport' => $import->toResource(),
            'headers' => $import->headers,
        ])->withViewData(compact('title'));
    }

    public function update(Request $request, InvoiceImport $import)
    {
        $this->authorize('update', InvoiceImport::class);

        $import->update([
            'mapping' => $request->all(),
        ]);

        session()->flash('success', __('Invoice import mapping saved successfully.'));

        return redirect()->route('invoices.imports.index');
    }
}
