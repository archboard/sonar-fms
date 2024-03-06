<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class DownloadInvoicePdfController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice', $invoice);

        $pdf = $invoice->latestPdf($request->boolean('force'));

        return Invoice::getPdfDisk()
            ->download($pdf->relative_path, $invoice->invoice_number.'.pdf');
    }
}
