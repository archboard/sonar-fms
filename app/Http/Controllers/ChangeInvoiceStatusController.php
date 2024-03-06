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
                Rule::in(['paid_at', 'voided_at']),
            ],
            'duplicate' => [
                'boolean',
                'required_if:status,voided_at',
            ],
        ]);

        $invoice->update([
            $data['status'] => now(),
        ]);

        session()->flash('success', __('Invoice status updated.'));

        if ($invoice->voided_at && $data['duplicate']) {
            return redirect()->route('invoices.duplicate', $invoice);
        }

        return back();
    }
}
