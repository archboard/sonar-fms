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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, School $school, InvoicePayment $payment)
    {
        $this->authorize('viewAny', $payment);

        $payment->fullLoad();
        $title = __('Receipt of payment to :invoice', [
            'invoice' => $payment->invoice->invoice_number,
        ]);

        return view('receipt', [
            'title' => $title,
            'payment' => $payment->toResource(),
            'layout' => $school->getDefaultReceiptLayout(),
        ]);
    }
}
