<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

class UpdateBatchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param UpdateInvoiceRequest $request
     * @param string $batch
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(UpdateInvoiceRequest $request, string $batch)
    {
        $this->authorize('update', Invoice::class);

        $results = InvoiceFromRequestFactory::make($request)
            ->build();

        // Delete the original invoice batch
        Invoice::batch($batch)
            ->unpublished()
            ->delete();

        return Invoice::successfullyUpdatedResponse($results);
    }
}
