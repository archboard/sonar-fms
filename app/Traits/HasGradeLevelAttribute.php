<?php

namespace App\Traits;

trait HasGradeLevelAttribute
{
    public function getGradeLevelShortFormattedAttribute(): string
    {
        if (is_null($this->grade_level)) {
            return '';
        }

        if ($this->grade_level > 0) {
            return (string) $this->grade_level;
        }

        if ($this->grade_level === 0) {
            return __('K');
        }

        return __('PK:age', ['age' => 5 + $this->grade_level]);
    }

    public function getGradeLevelFormattedAttribute(): string
    {
        if (is_null($this->grade_level)) {
            return '';
        }

        if ($this->grade_level > 0) {
            return __('Grade :grade', ['grade' => $this->grade_level]);
        }

        if ($this->grade_level === 0) {
            return __('Kindergarten');
        }

        return __('Pre-Kindergarten age :age', ['age' => 5 + $this->grade_level]);
    }
}
