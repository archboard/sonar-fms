<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\IsFileImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperPaymentImport
 */
class PaymentImport extends Model
{
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToUser;
    use IsFileImport;

    protected $guarded = [];

    protected $casts = [
        'mapping' => 'json',
        'results' => 'json',
        'total_records' => 'int',
        'imported_records' => 'int',
        'failed_records' => 'int',
        'heading_row' => 'int',
        'starting_row' => 'int',
        'imported_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where('file_path', 'ilike', "/%{$search}%");
        });
    }

    public static function storeFile(UploadedFile $file, School $school): string
    {
        $now = now()->format('U') . '-' . Str::random(8);

        return $file->storeAs(
            "payments/{$school->id}/{$now}",
            $file->getClientOriginalName()
        );
    }
}
