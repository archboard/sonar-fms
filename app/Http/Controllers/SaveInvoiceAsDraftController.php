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
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(CreateInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $results = InvoiceFromRequestFactory::make($request)
            ->asDraft()
            ->build();

        return Invoice::successfullyCreatedResponse($results);
    }
}
