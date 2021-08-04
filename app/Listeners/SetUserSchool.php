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

        if (
            !$user->school_id ||
            !$user->schools->contains('id', $user->school_id)
        ) {
            $school = $user->schools->first();

            if (!$school) {
                throw new \Exception("You do not have any schools configured for your account. Please contact your district admin for help.");
            }

            $user->update(['school_id' => $school->id]);
        }
    }
}
