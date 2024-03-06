<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReceiptResource;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Http\Request;

class InvoiceReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('view', Receipt::class);

        $receipts = $invoice->receipts()
            ->orderBy('created_at')
            ->with('user', 'invoicePayment')
            ->get();

        return ReceiptResource::collection($receipts);
    }
}
