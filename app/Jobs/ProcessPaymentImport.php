<?php

namespace App\Jobs;

use App\Factories\PaymentFromImportFactory;
use App\Models\PaymentImport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentImport
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected PaymentImport $import, protected User $user)
    {
        //
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

        PaymentFromImportFactory::make($this->import, $this->user)
            ->build();
    }
}
