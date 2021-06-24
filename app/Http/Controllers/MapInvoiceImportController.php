<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class MapInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, InvoiceImport $import)
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
}
