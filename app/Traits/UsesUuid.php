<?php

namespace App\Traits;

trait UsesUuid
{
    protected $keyType = 'string';

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
