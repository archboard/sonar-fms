<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use App\Models\School;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param InvoicePayment $payment
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, InvoicePayment $payment)
    {
        $this->authorize('view', $payment);

        return $payment->receiptView();
    }
}
