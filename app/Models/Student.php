<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\UsesUuid;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * @mixin IdeHelperStudent
 */
class Student extends Model implements Searchable
{
    use HasResource;
    use HasFactory;
    use BelongsToTenant;
    use UsesUuid;

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
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where(DB::raw("concat(first_name, ' ', last_name)"), 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "${search}%")
                    ->orWhere('student_number', 'ilike', "${search}%");
            });
        })->when($filters['grades'] ?? null, function (Builder $builder, $grades) {
            $builder->whereIn('grade_level', $grades);
        })->when(isset($filters['ids']), function (Builder $builder) use ($filters) {
            $builder->whereIn('uuid', $filters['ids']);
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

    public function scopeSisId(Builder $builder, $sisId)
    {
        $builder->where('sis_id', $sisId);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAccountBalanceAttribute(): int
    {
        return $this->invoices()
            ->isNotVoid()
            ->published()
            ->unpaid()
            ->sum('remaining_balance');
    }

    public function getUnpaidInvoicesAttribute(): int
    {
        return $this->invoices()
            ->isNotVoid()
            ->published()
            ->unpaid()
            ->count();
    }

    public function getRevenueAttribute(): int
    {
        return $this->invoices()
            ->isNotVoid()
            ->published()
            ->paymentMade()
            ->sum('pre_tax_subtotal');
    }

    public function getGradeLevelShortFormattedAttribute()
    {
        if ($this->grade_level > 0) {
            return $this->grade_level;
        }

        if ($this->grade_level === 0) {
            return __('K');
        }

        return __('PK:age', ['age' => 5 + $this->grade_level]);
    }

    public function getGradeLevelFormattedAttribute()
    {
        if ($this->grade_level > 0) {
            return __('Grade :grade', ['grade' => $this->grade_level]);
        }

        if ($this->grade_level === 0) {
            return __('Kindergarten');
        }

        return __('Pre-Kindergarten age :age', ['age' => 5 + $this->grade_level]);
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
    public function syncGuardians(Collection $schools = null): array
    {
        $response = PowerSchool::get("/ws/contacts/student/{$this->sis_id}");

        if (!$schools) {
            $schools = School::select(['id', 'school_number'])
                ->get()
                ->keyBy('school_number');
        }

        $users = collect($response)->reduce(function ($users, $contact) use ($schools) {
            if (empty($contact->emails)) {
                return $users;
            }

            // Get the primary or first email address
            $email = Arr::first(
                $contact->emails,
                fn ($emails) => $emails->primary,
                Arr::first($contact->emails)
            )->address;

            // If no name or email, don't process this relationship
            if (!$contact->firstName || !$contact->lastName || !$email) {
                return $users;
            }

            $contactStudents = Arr::first($contact->contactStudents);
            $details = Arr::first($contactStudents->studentDetails);

            /** @var User $user */
            $user = User::updateOrCreate([
                'tenant_id' => $this->tenant_id,
                'email' => strtolower($email),
            ], [
                'first_name' => $contact->firstName,
                'last_name' => $contact->lastName,
                'school_id' => optional($schools->get($contactStudents->schoolNumber))->id,
                'contact_id' => $contact->contactId,
                'guardian_id' => $contactStudents->guardianId,
            ]);

            $user->assign('guardian');

            $users[$user->id] = [
                'relationship' => optional($details)->relationship,
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
}
