<?php

namespace App\Jobs;

use App\Models\School;
use App\Notifications\SchoolSyncFinished;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SyncSchool implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var School
     */
    public $school;
    public bool $notify = false;

    // Set the timeout to be 10 minutes
    // in the case of large schools
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param School $school
     * @param bool $notify
     */
    public function __construct(School $school, bool $notify = false)
    {
        $this->school = $school;
        $this->notify = $notify;
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

//        $this->school->syncDataFromSis();

        if ($this->notify) {
            collect($this->school->tenant->getSyncNotificationEmails())
                ->each(function ($email) {
                    Notification::route('mail', $email)
                        ->notify(new SchoolSyncFinished($this->school));
                });
        }
    }
}
