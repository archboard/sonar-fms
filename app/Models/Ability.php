<?php

namespace App\Models;

use App\Traits\CreatesUuidForId;
use Silber\Bouncer\Database\Ability as Model;

/**
 * @mixin IdeHelperAbility
 */
class Ability extends Model
{
    use CreatesUuidForId;

    protected $casts = [
        'only_owned' => 'boolean',
    ];
}
