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
        return __($description, $this->properties->toArray());
    }

    public function getComponentAttribute():? string
    {
        return $this->properties->get('component');
    }
}
