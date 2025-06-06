<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class PreviewInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $invoice->fullLoad()->loadChildren();
        $layout = $request->school()->getDefaultInvoiceLayout();

        $title = __('Invoice #:number', ['number' => $invoice->id]);

        return view('invoice', [
            'layout' => $layout,
            'invoice' => $invoice,
            'title' => $title,
            'currency' => $invoice->currency,
        ]);
    }
}
