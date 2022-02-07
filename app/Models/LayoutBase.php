<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLayoutBase
 */
class LayoutBase extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;
    use BelongsToSchool;

    protected $guarded = [];

    protected $casts = [
        'layout_data' => 'json',
        'is_default' => 'boolean',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where('name', 'ilike', "%{$search}%");
        });

        $orderBy = $filters['orderBy'] ?? 'name';
        $orderDir = $filters['orderDir'] ?? 'asc';

        $builder->orderBy($orderBy, $orderDir);
        $builder->orderBy('name', $orderDir);
    }

    public function scopeDefault(Builder $builder, bool $status = true)
    {
        $builder->where('is_default', $status);
    }

    public function getMaxWidthAttribute(): string
    {
        $pages = [
            'Letter' => '8.5in',
            'A4' => '8.27in',
        ];

        return $pages[$this->paper_size];
    }
}
