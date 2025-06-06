<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetInvoiceRemainingBalance implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    final public function __construct(
        protected string $invoiceUuid,
        protected bool $distributeToTerms = true
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $batch = $this->batch();

        if ($batch?->cancelled()) {
            return;
        }

        /** @var Invoice $invoice */
        $invoice = Invoice::find($this->invoiceUuid);
        $invoice->setRemainingBalance()
            ->save();

        if ($this->distributeToTerms && ! $invoice->parent_uuid) {
            $invoice->distributePaymentsToTerms(true);
        }

        // If the original invoice was a child
        // dispatch calculating the parents data
        if ($invoice->parent_uuid) {
            $batch?->add([
                new static($invoice->parent_uuid),
            ]);
        }
    }
}
