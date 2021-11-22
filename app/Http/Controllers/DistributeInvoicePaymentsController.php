<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class DistributeInvoicePaymentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->distributePaymentsToTerms(true)
            ->setRemainingBalance();

        session()->flash('success', __('Remaining balances calculated successfully.'));

        return back();
    }
}
