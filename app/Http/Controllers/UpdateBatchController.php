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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
