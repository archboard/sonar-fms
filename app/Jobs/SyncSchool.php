<?php

namespace App\Jobs;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSchool
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var School
     */
    public $school;

    /**
     * Create a new job instance.
     *
     * @param School $school
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->school->syncDataFromSis();
    }
}
