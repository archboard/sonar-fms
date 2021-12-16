<?php

namespace App\Http\Controllers;

use App\Models\PaymentImport;
use Illuminate\Http\Request;

class RollBackPaymentImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, PaymentImport $import)
    {
        $this->authorize('roll back', $import);

        $import->rollBack();

        session()->flash('success', __('Import rolled back successfully.'));

        return redirect()->route('payments.imports.show', $import);
    }
}
