<?php

namespace App\Jobs;

use App\Models\School;
use App\Models\User;
use App\Notifications\SchoolSyncFinished;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SyncSchool implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $school;

    public $user;

    public bool $notify = false;

    // Set the timeout to be 10 minutes
    // in the case of large schools
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(School $school, bool $notify = false, ?User $user = null)
    {
        $this->school = $school;
        $this->notify = $notify;
        $this->user = $user;
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

        if (! app()->environment('testing')) {
            $this->school->syncDataFromSis();
        }

        if ($this->notify) {
            if ($this->user) {
                $this->user->notify(new SchoolSyncFinished($this->school));
            } else {
                collect($this->school->tenant->getSyncNotificationEmails())
                    ->each(function ($email) {
                        Notification::route('mail', $email)
                            ->notify(new SchoolSyncFinished($this->school));
                    });
            }
        }
    }
}
