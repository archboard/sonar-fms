<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;

class InvoiceImport extends Model
{
    use HasResource;
    use BelongsToSchool;
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'mapping' => 'json',
        'total_records' => 'int',
        'imported_records' => 'int',
        'failed_records' => 'int',
        'imported_at' => 'date',
    ];
}
