<?php

namespace App\Traits;

use App\Scopes\SchoolScope;

trait ScopeToCurrentSchool
{
    public static function bootScopeToCurrentSchool()
    {
        static::addGlobalScope(new SchoolScope);
    }
}
