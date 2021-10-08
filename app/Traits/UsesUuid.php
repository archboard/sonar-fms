<?php

namespace App\Traits;

trait UsesUuid
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getPrimaryKey(): string
    {
        return 'uuid';
    }

    public function getKeyName(): string
    {
        return 'uuid';
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getIdAttribute(): string
    {
        return $this->uuid;
    }
}
