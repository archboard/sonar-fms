<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLogin implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     */
    public function handle(Login $event)
    {
        // __(':user logged in successfully.')
        activity('auth')
            ->on($event->user)
            ->withProperties([
                'user' => $event->user->full_name,
            ])
            ->log(':user logged in successfully.');
    }
}
