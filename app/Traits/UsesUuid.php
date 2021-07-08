<?php

namespace App\Traits;

trait UsesUuid
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
