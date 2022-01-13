<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceLayout;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected string $invoiceUuid, protected ?int $invoiceLayoutId = null)
    {
        $this->onQueue('pdf');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $invoice = Invoice::find($this->invoiceUuid);

        if (!$invoice->published_at) {
            return;
        }

        $layout = $this->invoiceLayoutId
            ? InvoiceLayout::find($this->invoiceLayoutId)
            : null;

        $invoice->savePdf($layout);
    }
}
