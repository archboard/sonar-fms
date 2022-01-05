<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @mixin IdeHelperActivity
 */
class Activity extends BaseActivity
{
    public function getDescriptionAttribute($description):? string
    {
        $properties = collect($this->properties); // @phpstan-ignore-line

        if ($this->relationLoaded('causer')) {
            $properties->put('user', $this->causer->full_name);
        }

        return __($description, $properties->toArray());
    }

    public function getComponentAttribute():? string
    {
        return $this->properties->get('component'); // @phpstan-ignore-line
    }
}
