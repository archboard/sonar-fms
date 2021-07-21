<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class RollBackInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('roll back', $import);

        $import->rollBack();

        session()->flash('success', __('Invoice import rolled back successfully.'));

        return redirect()->route('invoices.imports.show', $import);
    }
}
