<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\CreateInvoiceRequest;
use App\Models\Invoice;

class UpdateDraftInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param CreateInvoiceRequest $request
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(CreateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('create', Invoice::class);

        $results = InvoiceFromRequestFactory::make($request)
            ->asDraft()
            ->build();

        $invoice->delete();

        return Invoice::successfullyUpdatedResponse($results);
    }
}
