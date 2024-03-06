<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use App\Models\PaymentImport;
use Illuminate\Http\Request;

class PreviewPaymentImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, PaymentImport $import)
    {
        $this->authorize('create', InvoicePayment::class);

        return redirect()->route('payments.imports.show', [
            'import' => $import,
            'preview' => 1,
        ]);
    }
}
