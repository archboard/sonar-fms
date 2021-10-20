<?php

namespace App\Traits;

use App\Factories\UuidFactory;

trait UsesUuid
{
    public static function bootUsesUuid()
    {
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = UuidFactory::make();
            }
        });
    }

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
