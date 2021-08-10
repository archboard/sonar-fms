<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class PublishInvoiceController extends Controller
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

        if ($invoice->published_at) {
            session()->flash('error', __('This invoice is already published.'));
            return back();
        }

        $invoice->publish();

        session()->flash('success', __('Invoice published successfully.'));

        return back();
    }
}
