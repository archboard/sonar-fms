<?php

namespace App\Models;

use App\Traits\CreatesUuidForId;
use Silber\Bouncer\Database\Role as Model;

class Role extends Model
{
    use CreatesUuidForId;
}
