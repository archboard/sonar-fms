<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessInvoiceImport;
use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class StartInvoiceImport extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('create', $import);

        if (!$import->mapping_valid) {
            session()->flash('error', __('Import mapping is incomplete.'));

            return redirect()->route('invoices.imports.map', $import);
        }

        $this->dispatch(new ProcessInvoiceImport($import));

        session()->flash('success', __('Invoice import started successfully.'));

        return redirect()->route('invoices.imports.show', $import);
    }
}
