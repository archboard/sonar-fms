<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PruneInvoicePdfs implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $directory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected string $path)
    {
        $this->onQueue('pdf');

        $this->directory = dirname($path);
    }

    public function uniqueId(): string
    {
        return $this->directory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Invoice::getPdfDisk()
            ->deleteDirectory($this->directory);
    }
}
