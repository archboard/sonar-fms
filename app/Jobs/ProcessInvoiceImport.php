<?php

namespace App\Jobs;

use App\Events\InvoiceImportFinished;
use App\Factories\InvoiceFromImportFactory;
use App\Models\InvoiceImport;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInvoiceImport
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected InvoiceImport $import;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(InvoiceImport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->import->update([
            'mapping_valid' => $this->import->hasValidMapping(),
            'rolled_back_at' => null,
        ]);

        InvoiceFromImportFactory::make($this->import)
            ->build();

        event(new InvoiceImportFinished($this->import));
    }
}
