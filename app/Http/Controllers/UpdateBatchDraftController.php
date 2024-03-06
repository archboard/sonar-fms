<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

class UpdateBatchDraftController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(UpdateInvoiceRequest $request, string $batch)
    {
        $this->authorize('update', Invoice::class);

        $results = InvoiceFromRequestFactory::make($request)
            ->asDraft()
            ->build();

        // Delete the original batch of unpublished
        Invoice::batch($batch)
            ->unpublished()
            ->delete();

        return Invoice::successfullyUpdatedResponse($results);
    }
}
