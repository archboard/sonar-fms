<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
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
use NumberFormatter;

/**
 * App\Models\Student
 *
 * @mixin IdeHelperStudent
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $sis_id
 * @property string|null $student_number
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property int|null $grade_level
 * @property bool $enrolled
 * @property int $enroll_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $preferred_name
 * @property \Illuminate\Support\Carbon|null $current_entry_date
 * @property \Illuminate\Support\Carbon|null $current_exit_date
 * @property \Illuminate\Support\Carbon|null $initial_district_entry_date
 * @property \Illuminate\Support\Carbon|null $initial_school_entry_date
 * @property string|null $initial_district_grade_level
 * @property string|null $initial_school_grade_level
 * @property-read mixed $full_name
 * @property-read mixed $grade_level_formatted
 * @property-read mixed $grade_level_short_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $guardians
 * @property-read int|null $guardians_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\StudentFactory factory(...$parameters)
 * @method static Builder|Student filter(array $filters)
 * @method static Builder|Student newModelQuery()
 * @method static Builder|Student newQuery()
 * @method static Builder|Student query()
 * @method static Builder|Student sisId($sisId)
 * @method static Builder|Student whereCreatedAt($value)
 * @method static Builder|Student whereCurrentEntryDate($value)
 * @method static Builder|Student whereCurrentExitDate($value)
 * @method static Builder|Student whereEmail($value)
 * @method static Builder|Student whereEnrollStatus($value)
 * @method static Builder|Student whereEnrolled($value)
 * @method static Builder|Student whereFirstName($value)
 * @method static Builder|Student whereGradeLevel($value)
 * @method static Builder|Student whereId($value)
 * @method static Builder|Student whereInitialDistrictEntryDate($value)
 * @method static Builder|Student whereInitialDistrictGradeLevel($value)
 * @method static Builder|Student whereInitialSchoolEntryDate($value)
 * @method static Builder|Student whereInitialSchoolGradeLevel($value)
 * @method static Builder|Student whereLastName($value)
 * @method static Builder|Student wherePreferredName($value)
 * @method static Builder|Student whereSchoolId($value)
 * @method static Builder|Student whereSisId($value)
 * @method static Builder|Student whereStudentNumber($value)
 * @method static Builder|Student whereTenantId($value)
 * @method static Builder|Student whereUpdatedAt($value)
 */
class Student extends Model
{
    use HasResource;
    use HasFactory;
    use BelongsToTenant;

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
        });

        // Enrollment status
        $enrolled = $filters['enrolled'] ?? true;

        if ($enrolled !== 'all') {
            $builder->where('enrolled', $enrolled);
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

    public function getGradeLevelShortFormattedAttribute()
    {
        if ($this->grade_level > 0) {
            return $this->grade_level;
        }

        if ($this->grade_level === 0) {
            return __('K');
        }

        return __('PK:age', ['age' => 5 - $this->grade_level]);
    }

    public function getGradeLevelFormattedAttribute()
    {
        if ($this->grade_level > 0) {
            return __('Grade :grade', ['grade' => $this->grade_level]);
        }

        if ($this->grade_level === 0) {
            return __('Kindergarten');
        }

        return __('Pre-Kindergarten age :age', ['age' => 5 - $this->grade_level]);
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

            $users[$user->id] = [
                'relationship' => optional($details)->relationship,
            ];

            return $users;
        }, []);

        $this->users()->syncWithoutDetaching($users);

        return $users;
    }
}
