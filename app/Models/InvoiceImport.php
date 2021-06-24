<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperInvoiceImport
 */
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
        'heading_row' => 'int',
        'starting_row' => 'int',
        'imported_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
        });
    }

    public function getAbsolutePathAttribute(): string
    {
        return Storage::path($this->file_path);
    }

    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    public static function storeFile(UploadedFile $file, School $school): string
    {
        $now = now()->format('U') . '-' . Str::random(8);

        return $file->storeAs(
            "imports/{$school->id}/{$now}",
            $file->getClientOriginalName()
        );
    }
}
