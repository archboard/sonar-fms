<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

class UpdateDraftInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $results = InvoiceFromRequestFactory::make($request)
            ->asDraft()
            ->withOriginalBatchId($invoice->batch_id)
            ->withUpdateActivityDescription()
            ->build();

        return Invoice::successfullyUpdatedResponse($results);
    }
}
