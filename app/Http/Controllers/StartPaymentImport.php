<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentImport;
use App\Models\InvoicePayment;
use App\Models\PaymentImport;
use Illuminate\Http\Request;

class StartPaymentImport extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, PaymentImport $import)
    {
        $this->authorize('create', InvoicePayment::class);

        if (!$import->mapping_valid) {
            session()->flash('error', __('Import mapping is incomplete.'));
            return redirect()->route('payments.imports.map', $import);
        }

        $this->dispatch(new ProcessPaymentImport($import, $request->user()));

        session()->flash('success', __('Invoice import started successfully.'));

        return redirect()->route('payments.imports.show', $import);
    }
}
