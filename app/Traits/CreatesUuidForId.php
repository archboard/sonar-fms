<?php

namespace App\Traits;

use App\Factories\UuidFactory;

trait CreatesUuidForId
{
    public static function bootCreatesUuid()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = UuidFactory::make();
            }
        });
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getIncrementing(): bool
    {
        return false;
    }
}
