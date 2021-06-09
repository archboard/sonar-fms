<?php

namespace App\Factories;

use Ramsey\Uuid\Uuid;

class UuidFactory
{
    public static function make(): string
    {
        return Uuid::uuid4()->toString();
    }
}
