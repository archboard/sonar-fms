<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSchools implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tenant
     */
    public $tenant;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! app()->environment('testing')) {
            $this->tenant->sisProvider()->syncSchools();
        }

        $schools = $this->tenant->schools->pluck('id');

        // Sync super admins with these schools
        $this->tenant
            ->users()
            ->where('manages_tenancy', true)
            ->get()
            ->each(function (User $user) use ($schools) {
                $user->schools()->sync($schools);
            });
    }
}
