<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\CreateInvoiceRequest;
use App\Models\Invoice;

class SaveInvoiceAsDraftController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(CreateInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $results = InvoiceFromRequestFactory::make($request)
            ->asDraft()
            ->build();

        if ($results->count() === 1) {
            session()->flash('success', __('Invoice created successfully.'));
        } else {
            session()->flash('success', __(':count invoices created successfully.', [
                'count' => $results->count(),
            ]));
        }

        $invoice = Invoice::where('uuid', $results->first())
            ->first();

        return redirect()->route('invoices.index', ['batch_id' => $invoice->batch_id]);
    }
}
