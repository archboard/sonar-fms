<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class RollBackInvoiceImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('roll back', $import);

        $import->rollBack();

        session()->flash('success', __('Import rolled back successfully.'));

        return redirect()->route('invoices.imports.show', $import);
    }
}
