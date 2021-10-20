<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class RemoveChildInvoiceController extends Controller
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

        $invoice->update(['parent_uuid' => null]);

        session()->flash('success', __('Invoice removed successfully.'));

        return back();
    }
}
