<?php

namespace App\Traits;

trait AddsNotificationSubject
{
    public function makeSubject(string $subject): string
    {
        return '[Sonar FMS] '.$subject;
    }
}
