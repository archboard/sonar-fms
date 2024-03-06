<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceLayout;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoicePdf implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected string $invoiceUuid, protected ?int $invoiceLayoutId = null, protected bool $force = false)
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
        $batch = $this->batch();

        if ($batch && $batch->cancelled()) {
            return;
        }

        $invoice = Invoice::find($this->invoiceUuid);

        if (! $invoice->published_at && ! $this->force) {
            return;
        }

        $layout = $this->invoiceLayoutId
            ? InvoiceLayout::find($this->invoiceLayoutId)
            : null;

        $invoice->savePdf($layout);
    }
}
