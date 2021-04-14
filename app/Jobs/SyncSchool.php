<?php

namespace App\Jobs;

use App\Models\School;
use App\Notifications\SchoolSyncFinished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SyncSchool implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var School
     */
    public $school;
    public bool $notify = false;

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
        $this->school->syncDataFromSis();

        if ($this->notify) {
            collect($this->school->tenant->getSyncNotificationEmails())
                ->each(function ($email) {
                    Notification::route('mail', $email)
                        ->notify(new SchoolSyncFinished($this->school));
                });
        }
    }
}
