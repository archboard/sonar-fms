<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class SetUserSchool
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
        $user->setSchool()
            ->save();
    }
}
