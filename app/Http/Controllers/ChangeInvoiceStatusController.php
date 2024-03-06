<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChangeInvoiceStatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['paid_at', 'voided_at', 'canceled_at']),
            ],
            'duplicate' => [
                'boolean',
                'required_if:status,voided_at',
            ],
        ]);

        $invoice->fill([
            $data['status'] => now(),
        ]);

        if ($invoice->canceled_at) {
            $invoice->remaining_balance = 0;
            $invoice->invoicePaymentTerms()
                ->update(['remaining_balance' => 0]);
        }

        $invoice->save();

        session()->flash('success', __('Invoice status updated.'));

        if ($invoice->voided_at && $data['duplicate']) {
            return redirect()->route('invoices.duplicate', $invoice);
        }

        return back();
    }
}
