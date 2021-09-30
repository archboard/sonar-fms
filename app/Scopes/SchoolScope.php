<?php

namespace App\Scopes;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if ($school = app(School::class)) {
            $builder->where("{$model->getTable()}.school_id", $school->id);
        }
    }

    public function extend(Builder $builder)
    {
        $this->addWithoutSchool($builder);
    }

    protected function addWithoutSchool(Builder $builder)
    {
        $builder->macro('withoutSchool', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
