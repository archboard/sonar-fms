<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class DownloadInvoicePdfController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice', $invoice);

        $invoice->load([
            'invoiceScholarships.appliesTo',
            'invoicePaymentSchedules.invoicePaymentTerms',
        ]);
        $layout = $request->school()->getDefaultInvoiceLayout();
        $title = __('Invoice #:number', ['number' => $invoice->id]);

        $content = view('invoice', [
            'layout' => $layout,
            'invoices' => [$invoice],
            'title' => $title,
            'currency' => $invoice->currency,
        ])->render();

        $browserShot = Browsershot::html($content)
            ->disableJavascript()
            ->margins(0, 0, 0, 0)
            ->format($layout->paper_size)
            ->noSandbox()
            ->showBackground()
            ->setNodeBinary(config('services.node.binary'))
            ->setNpmBinary(config('services.node.npm'))
            ->hideHeader()
            ->hideFooter();

        return response()->stream(function () use ($browserShot) {
            echo $browserShot->pdf();
        }, 200, [
            'Content-Disposition' => "inline; filename=\"invoice {$invoice->id}.pdf\"",
            'Content-Type' => 'application/pdf',
        ]);
    }
}
