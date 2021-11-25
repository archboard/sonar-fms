<?php

namespace Tests\Traits;

use App\Models\Term;

trait SeedsTerms
{
    protected function seedTerm(array $attributes = []): Term
    {
        return $this->school->terms()
            ->save(Term::factory()->make($attributes));
    }
}
