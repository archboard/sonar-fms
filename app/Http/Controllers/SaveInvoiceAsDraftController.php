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

        return Invoice::successfullyCreatedResponse($results);
    }
}
