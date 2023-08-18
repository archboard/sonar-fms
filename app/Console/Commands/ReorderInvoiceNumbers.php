<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReorderInvoiceNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reorder-invoice-numbers {school}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reorders the invoice numbers based on when the invoices were created.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $school = School::findOrFail($this->argument('school'));

        $data = $school->invoices()
            ->orderBy('id')
            ->get()
            ->reduce(function (array $carry, Invoice $invoice) {
                $carry[] = [
                    'uuid' => $invoice->uuid,
                    'tenant_id' => $invoice->tenant_id,
                    'school_id' => $invoice->school_id,
                    'title' => $invoice->title,
                    'invoice_date' => $invoice->invoice_date,
                    'invoice_number' => Str::padLeft(count($carry) + 1, 3, '0'),
                ];

                return $carry;
            }, []);

        Invoice::upsert($data, 'uuid', ['invoice_number']);
    }
}
