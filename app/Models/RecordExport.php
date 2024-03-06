<?php

namespace App\Models;

use App\Exports\RecordsExport;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * @mixin IdeHelperRecordExport
 */
class RecordExport extends Model
{
    use BelongsToSchool;
    use BelongsToUser;
    use HasFactory;
    use Prunable;

    protected $guarded = [];

    protected $casts = [
        'apply_filters' => 'boolean',
        'filters' => 'json',
    ];

    public function fileName(): Attribute
    {
        return Attribute::get(
            fn (): string => "{$this->name}.{$this->format}"
        );
    }

    public function download(): RecordsExport
    {
        return new RecordsExport($this);
    }
}
