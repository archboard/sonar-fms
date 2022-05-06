<?php

namespace App\Models;

use App\Concerns\Exportable;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\UsesUuid;
use BeyondCode\Comments\Traits\HasComments;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Spatie\Tags\HasTags;

/**
 * @mixin IdeHelperStudent
 */
class Student extends Model implements Searchable, Exportable
{
    use HasResource;
    use HasFactory;
    use BelongsToTenant;
    use BelongsToSchool;
    use UsesUuid;
    use HasComments;
    use HasTags;

    protected $guarded = [];

    protected $casts = [
        'grade_level' => 'integer',
        'current_entry_date' => 'date',
        'current_exit_date' => 'date',
        'initial_district_entry_date' => 'date',
        'initial_school_entry_date' => 'date',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->search($search); // @phpstan-ignore-line
        })->when($filters['grades'] ?? null, function (Builder $builder, $grades) {
            $builder->whereIn('grade_level', $grades);
        })->when(isset($filters['ids']), function (Builder $builder) use ($filters) {
            $builder->whereIn('uuid', $filters['ids']);
        })->when($filters['tags'] ?? null, function (Builder $builder, array $tags) {
            $builder->withAnyTags($tags, Tag::student(School::current()));
        })->when($filters['exclude'] ?? null, function (Builder $builder, $exclude) {
            $builder->whereNotIn('uuid', Arr::wrap($exclude));
        });

        // Enrollment status
        $status = $filters['status'] ?? 'enrolled';

        if ($status !== 'all') {
            $builder->where('enrolled', $status === 'enrolled');
        }

        $orderBy = $filters['orderBy'] ?? 'last_name';

        // Cast as int so postgres sorts correctly
        if ($orderBy === 'grade_level') {
            $orderBy = DB::raw('grade_level::int');
        }

        $builder->orderBy($orderBy, $filters['orderDir'] ?? 'asc');

        // Add the secondary first name order column
        $builder->orderBy('first_name', $filters['orderDir'] ?? 'asc');
    }

    public function scopeSearch(Builder $builder, string $search)
    {
        $builder->where(function (Builder $builder) use ($search) {
                $builder->where(DB::raw("concat(first_name, ' ', last_name)"), 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "${search}%")
                    ->orWhere('student_number', 'ilike', "${search}%");
            });
    }

    public function scopeSisId(Builder $builder, $sisId)
    {
        $builder->where('sis_id', $sisId);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function unpaidInvoices(): Attribute
    {
        return Attribute::get(fn (): int => $this->invoices()
            ->isNotVoid()
            ->published()
            ->unpaid()
            ->count()
        );
    }

    public function getGradeLevelShortFormattedAttribute(): string
    {
        if ($this->grade_level > 0) {
            return (string) $this->grade_level;
        }

        if ($this->grade_level === 0) {
            return __('K');
        }

        return __('PK:age', ['age' => 5 + $this->grade_level]);
    }

    public function getGradeLevelFormattedAttribute(): string
    {
        if ($this->grade_level > 0) {
            return __('Grade :grade', ['grade' => $this->grade_level]);
        }

        if ($this->grade_level === 0) {
            return __('Kindergarten');
        }

        return __('Pre-Kindergarten age :age', ['age' => 5 + $this->grade_level]);
    }

    public function accountBalanceFormatted(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->relationLoaded('currency')) {
                return displayCurrency($this->account_balance, $this->currency);
            }

            return '';
        });
    }

    public function revenueFormatted(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->relationLoaded('currency')) {
                return displayCurrency($this->revenue, $this->currency);
            }

            return '';
        });
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('relationship');
    }

    public function guardians(): BelongsToMany
    {
        return $this->users();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function syncFromSis()
    {
        $this->tenant->sisProvider()
            ->syncStudent($this);
    }

    /**
     * Syncs guardians for a student
     * The contact account MUST have
     * first name, last name and an email to be synced
     *
     * @throws \GrantHolle\PowerSchool\Api\Exception\MissingClientCredentialsException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncContacts(Collection $schools = null): array
    {
        $response = PowerSchool::get("/ws/contacts/student/{$this->sis_id}");

        if (!$schools) {
            $schools = School::select(['id', 'school_number'])
                ->get()
                ->keyBy('school_number');
        }

        $users = $response->collect()
            ->reduce(function ($users, $contact) use ($schools) {
                if (empty($contact['emails'])) {
                    return $users;
                }

                // Get the primary or first email address
                $email = Arr::first(
                    $contact['emails'],
                    fn ($emails) => $emails['primary'],
                    Arr::first($contact['emails'])
                )['address'];

                // If no name or email, don't process this relationship
                if (!$contact['firstName'] || !$contact['lastName'] || !$email) {
                    return $users;
                }

                $contactStudents = Arr::first($contact['contactStudents']);
                $details = Arr::first($contactStudents['studentDetails']);

                /** @var User $user */
                $user = User::updateOrCreate([
                    'tenant_id' => $this->tenant_id,
                    'email' => strtolower($email),
                ], [
                    'first_name' => $contact['firstName'],
                    'last_name' => $contact['lastName'],
                    'school_id' => optional($schools->get($contactStudents['schoolNumber']))->id,
                    'contact_id' => $contact['contactId'],
                    'guardian_id' => $contactStudents['guardianId'],
                ]);

                $user->assign('contact');

                $users[$user->id] = [
                    'relationship' => $details['relationship'] ?? null,
                ];

                return $users;
            }, []);

        $this->users()->syncWithoutDetaching($users);

        return $users;
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->full_name,
            route('students.show', $this)
        );
    }

    public function setAccountBalance(): static
    {
        $this->account_balance = $this->invoices()
            ->isNotVoid()
            ->published()
            ->unpaid()
            ->sum('remaining_balance');

        return $this;
    }

    public function setRevenue(): static
    {
        $this->revenue = $this->invoices()
            ->isNotVoid()
            ->published()
            ->sum('total_paid');

        return $this;
    }

    public static function getExportHeadings(): array
    {
        return [
            __('Student number'),
            __('First name'),
            __('Last name'),
            __('Grade'),
            __('Account balance'),
            __('Payments/receipts'),
            __('Paid invoices'),
            __('Unpaid invoices'),
        ];
    }

    public function getExportRow(): array
    {
        return [
            $this->student_number,
            $this->first_name,
            $this->last_name,
            $this->grade_level_short_formatted,
            $this->account_balance / pow(10, $this->currency->digits),
            $this->revenue / pow(10, $this->currency->digits),
            $this->paid_invoices_count,
            $this->unpaid_invoices_count,
        ];
    }

    public static function getExportQuery(RecordExport $export): \Illuminate\Database\Query\Builder|Builder|Relation
    {
        $query = $export->school
            ->students()
            ->with('currency')
            ->withCount([
                'invoices as paid_invoices_count' =>
                    fn ($query) => $query->where('remaining_balance', 0)
                        ->isNotVoid()
                        ->published(),
                'invoices as unpaid_invoices_count' =>
                    fn ($query) => $query->where('remaining_balance', '>', 0)
                        ->isNotVoid()
                        ->published(),
            ])
            ->orderBy('last_name');

        if ($export->apply_filters) {
            $query->filter($export->filters);
        }

        return $query;
    }
}
