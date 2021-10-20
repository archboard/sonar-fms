<?php

namespace App\Models;

use App\Traits\CreatesUuidForId;
use Silber\Bouncer\Database\Ability as Model;

class Ability extends Model
{
    use CreatesUuidForId;
}
