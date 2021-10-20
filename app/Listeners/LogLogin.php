<?php

namespace App\Listeners;

use App\Models\User;
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
        /** @var User $user */
        $user = $event->user;

        // __(':user logged in successfully.')
        activity('auth')
            ->on($user)
            ->withProperties([
                'user' => $user->full_name,
            ])
            ->log(':user logged in successfully.');
    }
}
