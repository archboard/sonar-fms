<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogFailedLogin implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Failed $event)
    {
        // __(':email attempted logging in but failed.')
        activity('auth')
            ->withProperties([
                'email' => $event->credentials['email'],
            ])
            ->log(':email attempted logging in but failed.');
    }
}
