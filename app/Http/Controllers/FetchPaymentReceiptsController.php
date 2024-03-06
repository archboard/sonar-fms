<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReceiptResource;
use App\Models\InvoicePayment;
use App\Models\Receipt;
use Illuminate\Http\Request;

class FetchPaymentReceiptsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, InvoicePayment $payment)
    {
        $this->authorize('view', Receipt::class);

        $receipts = $payment->receipts()
            ->orderBy('created_at')
            ->with('user', 'invoicePayment')
            ->get();

        return ReceiptResource::collection($receipts);
    }
}
