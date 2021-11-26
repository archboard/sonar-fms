<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceSearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::filter($request->all())
            ->with([
                'parent',
                'children',
                'children.student',
                'student',
                'students',
                'currency',
                'invoicePaymentSchedules.invoicePaymentTerms',
            ])
            ->limit(25)
            ->get();

        return InvoiceResource::collection($invoices);
    }
}
