<?php

namespace App\Models;

use App\Traits\CreatesUuidForId;
use Silber\Bouncer\Database\Role as Model;

/**
 * @mixin IdeHelperRole
 */
class Role extends Model
{
    use CreatesUuidForId;

    protected $casts = [
        'level' => 'int',
    ];
}
