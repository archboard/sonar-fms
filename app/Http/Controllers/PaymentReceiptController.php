<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, InvoicePayment $payment)
    {
        $this->authorize('view', $payment);

        return $payment->receiptView();
    }
}
