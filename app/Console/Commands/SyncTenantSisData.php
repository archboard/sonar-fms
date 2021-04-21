<?php

namespace App\Console\Commands;

use App\Jobs\SyncTenantSisData as SyncTenantSisDataJob;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SyncTenantSisData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets the tenants that need to sync';

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
        $hour = now()->format('G');

        $query = Tenant::whereHas('syncTimes', function (Builder $builder) use ($hour) {
            $builder->where('hour', $hour);
        });

        if ($query->count() === 0) {
            $this->info("No tenants sync at hour {$hour}.");
            return 0;
        }

        $query->cursor()
            ->each(function (Tenant $tenant) {
                $this->info("Dispatching job for {$tenant->name} from {$tenant->sis}.");
                $tenant->makeCurrent();
                SyncTenantSisDataJob::dispatch($tenant);
            });

        return 0;
    }
}
