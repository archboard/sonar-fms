<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInvoiceLayoutKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:invoice_layouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the key for invoice layouts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('invoice_layouts')
            ->get(['id', 'layout_data'])
            ->each(function ($layout) {
                $newLayout = str_replace('isInvoiceTable', 'isContentTable', $layout->layout_data);
                DB::table('invoice_layouts')
                    ->where('id', $layout->id)
                    ->update(['layout_data' => $newLayout]);
            });

        return 0;
    }
}
