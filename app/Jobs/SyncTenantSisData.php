<?php

namespace App\Jobs;

use App\Models\School;
use App\Models\Tenant;
use App\Notifications\TenantSyncComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SyncTenantSisData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tenant
     */
    public $tenant;

    /**
     * Create a new job instance.
     *
     * @return void
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
        $this->tenant->sisProvider()
            ->syncSchools()
            ->each(fn (School $school) => SyncSchool::dispatchSync($school));

        collect($this->tenant->getSyncNotificationEmails())
            ->each(function ($email) {
                Notification::route('mail', $email)
                    ->notify(new TenantSyncComplete());
            });
    }
}
