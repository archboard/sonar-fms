<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, InvoicePayment $payment)
    {
        $this->authorize('viewAny', $payment);

        $payment->fullLoad();
        $title = __('Receipt for payment to :invoice', [
            'invoice' => $payment->invoice->invoice_number,
        ]);

        return inertia('payments/Receipt', [
            'title' => $title,
            'payment' => $payment->toResource(),
        ])->withViewData(compact('title'));
    }
}
