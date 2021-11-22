<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasTaxRateAttribute;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperSchool
 */
class School extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use HasResource;
    use HasTaxRateAttribute;

    protected $guarded = [];

    protected $casts = [
        'active_at' => 'datetime',
        'collect_tax' => 'boolean',
        'tax_rate' => 'float',
    ];

    public function scopeActive(Builder $builder)
    {
        $builder->where('active', true);
    }

    public function scopeInactive(Builder $builder)
    {
        $builder->where('active', false);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['staff_id']);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(Scholarship::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceTemplates(): HasMany
    {
        return $this->hasMany(InvoiceTemplate::class);
    }

    public function invoiceImports(): HasMany
    {
        return $this->hasMany(InvoiceImport::class);
    }

    public function invoiceLayouts(): HasMany
    {
        return $this->hasMany(InvoiceLayout::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class)
            ->orderBy('driver');
    }

    public function getInvoiceNumberPrefix(?User $user = null): string
    {
        if (!$this->invoice_number_template) {
            return '';
        }

        $now = $user
            ? $user->getCarbonFactory()->now()
            : now();

        return Str::replace(
            ['{year}', '{month}'],
            [$now->format('Y'), $now->format('m')],
            $this->invoice_number_template
        );
    }

    public function getGradeLevelsAttribute(): array
    {
        return range($this->low_grade, $this->high_grade);
    }

    public function getDefaultInvoiceLayout(): ?InvoiceLayout
    {
        return $this->invoiceLayouts()
            ->default()
            ->first();
    }

    public function syncDataFromSis()
    {
        $this->tenant->sisProvider()->fullSchoolSync($this);
    }

    public static function current(): ?static
    {
        /** @var User|null $user */
        $user = auth()->user();

        /** @var School $school */
        if ($user && $school = $user->school) {
            return $school;
        }

        return null;
    }

    public function getPaymentMethods(): array
    {
        return PaymentMethod::getListForSchool($this);
    }
}
