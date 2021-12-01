<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\UsesUuid;
use Carbon\Factory;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Silber\Bouncer\Database\HasRolesAndAbilities;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements HasLocalePreference
{
    use HasFactory;
    use Notifiable;
    use HasResource;
    use HasRolesAndAbilities;
    use BelongsToTenant;
    use UsesUuid;

    const TEACHER = 'teacher';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'manages_tenancy' => 'boolean',
        'sis_id' => 'int',
        'contact_id' => 'int',
        'guardian_id' => 'int',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where(DB::raw("concat(first_name, ' ', last_name)"), 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "${search}%");
            });
        });

        $orderBy = $filters['orderBy'] ?? 'last_name';
        $builder->orderBy($orderBy, $filters['orderDir'] ?? 'asc');
        $builder->orderBy('first_name', $filters['orderDir'] ?? 'asc');
    }

    /**
     * Gets the users who have an ability directly or through a role
     *
     * @param Builder $query
     * @param string $ability
     */
    public function scopeWhereCan(Builder $query, string $ability)
    {
        $query->where(function ($query) use ($ability) {
            // direct
            $query->whereHas('abilities', function ($query) use ($ability) {
                $query->byName($ability);
            });
            // through roles
            $query->orWhereHas('roles', function ($query) use ($ability) {
                 $query->whereHas('abilities', function ($query) use ($ability) {
                     $query->byName($ability);
                 });
             });
         });
    }

    public function getSchoolPermissionsAttribute(): array
    {
        return $this->getPermissionsForSchool();
    }

    public function getStudentSelectionAttribute()
    {
        if ($this->relationLoaded('studentSelections')) {
            return $this->studentSelections->pluck('student_uuid');
        }

        return collect();
    }

    public function getInvoiceSelectionAttribute()
    {
        if ($this->relationLoaded('invoiceSelections')) {
            return $this->invoiceSelections->pluck('invoice_uuid');
        }

        return collect();
    }

    public function getIsSchoolAdminAttribute(): bool
    {
        return $this->isSchoolAdmin();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDateFactoryAttribute(): Factory
    {
        return new Factory([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ]);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['staff_id']);
    }

    public function activeSchools(): BelongsToMany
    {
        return $this->schools()
            ->where('active', true);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('relationship');
    }

    public function studentSelections(): HasMany
    {
        return $this->hasMany(StudentSelection::class)
            ->join('users', function (JoinClause $join) {
                $join->on('student_selections.user_uuid', '=', 'users.uuid');
            })
            ->whereRaw('student_selections.school_id = users.school_id');
    }

    public function selectedStudents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentSelection::class,
            'student_uuid',
            'id',
            'id',
            'user_uuid'
        );
    }

    public function getSelectedStudentsAttribute(): Collection
    {
        return Student::join('student_selections', 'student_uuid', '=', 'students.uuid')
            ->where('student_selections.school_id', $this->school_id)
            ->where('student_selections.user_uuid', $this->id)
            ->get();
    }

    public function invoiceSelections(): HasMany
    {
        return $this->hasMany(InvoiceSelection::class)
            ->join('users', function (JoinClause $join) {
                $join->on('invoice_selections.user_uuid', '=', 'users.uuid');
            })
            ->whereRaw('invoice_selections.school_id = users.school_id');
    }

    public function selectedInvoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            InvoiceSelection::class,
            'user_uuid',
            'uuid',
            'id',
            'invoice_uuid'
        )->where('invoice_selections.school_id', $this->school_id);
    }

    public function invoiceImports(): HasMany
    {
        return $this->hasMany(InvoiceImport::class);
    }

    public function paymentImports(): HasMany
    {
        return $this->hasMany(PaymentImport::class);
    }

    public function paymentImportTemplates(): HasMany
    {
        return $this->hasMany(PaymentImportTemplate::class);
    }

    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class);
    }

    public function getPermissionsForSchool(School $school = null): array
    {
        $school = $school ?? $this->school;

        return [
            [
                'label' => __('Change school settings'),
                'permission' => 'change settings',
                'selected' => $this->can('change settings', $school),
            ],
        ];
    }

    /**
     * Adds a student to a user's selection
     *
     * @param int|Student $studentId
     * @return User
     */
    public function selectStudent(Student|string $studentId): static
    {
        if ($studentId instanceof Student) {
            $studentId = $studentId->uuid;
        }

        $exists = $this->studentSelections()
            ->student($studentId)
            ->exists();

        if (!$exists) {
            DB::table('student_selections')->insert([
                'school_id' => $this->school_id,
                'user_uuid' => $this->id,
                'student_uuid' => $studentId,
            ]);
        }

        return $this;
    }

    public function selectInvoice(Invoice|string $invoiceUuid): static
    {
        if ($invoiceUuid instanceof Invoice) {
            $invoiceUuid = $invoiceUuid->uuid;
        }

        $exists = $this->invoiceSelections()
            ->invoice($invoiceUuid)
            ->exists();

        if (!$exists) {
            DB::table('invoice_selections')->insert([
                'school_id' => $this->school_id,
                'user_uuid' => $this->id,
                'invoice_uuid' => $invoiceUuid,
            ]);
        }

        return $this;
    }

    public function syncStudents()
    {
        if (!$this->contact_id) {
            return;
        }

        $response = PowerSchool::get("/ws/contacts/{$this->contact_id}/students");
        $contactStudents = collect($response);

        $students = Student::whereIn('sis_id', $contactStudents->pluck('dcid'))
            ->pluck('uuid', 'sis_id');
        $schools = School::whereIn('school_number', $contactStudents->pluck('schoolNumber'))
            ->pluck('id', 'school_number');
        $studentUser = DB::table('student_user')
            ->where('user_uuid', $this->uuid)
            ->pluck('student_uuid');

        // Attach the school relationship
        $this->schools()->syncWithoutDetaching($schools->values());

        $studentUsers = $contactStudents->reduce(function ($studentUsers, $student) use ($schools, $students, $studentUser) {
            $studentUuid = $students->get($student->dcid);

            // If the school doesn't exist
            // or the student doesn't exist
            // or we already have the relationship
            // don't do anything
            if (
                !$schools->has($student->schoolNumber) ||
                !$studentUuid ||
                $studentUser->contains($studentUuid)
            ) {
                return $studentUsers;
            }

            $studentUsers[] = [
                'student_uuid' => $studentUuid,
                'user_uuid' => $this->uuid,
                'relationship' => optional($student->studentDetails[0] ?? null)->relationship,
            ];

            return $studentUsers;
        }, []);

        ray($studentUsers)->green();
        DB::table('student_user')->insert($studentUsers);
    }

    public function setContactId(): static
    {
        if (!$this->contact_id && $this->guardian_id) {
            $response = PowerSchool::pq('com.archboard.sonarfms.guardian.contactid', ['guardianid' => $this->guardian_id]);

            if (isset($response->record) && count($response->record) === 1) {
                $this->update(['contact_id' => $response->record[0]->personid]);
            }
        }

        return $this;
    }

    /**
     * Gets all the schools of the tenancy and
     * a flag of whether the user has access
     *
     * Used for assigning schools in users
     *
     * @return Collection
     */
    public function getSchoolAccessList(): Collection
    {
        return School::orderBy('name')
            ->get()
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
                'active' => $school->active,
                'has_access' => $this->schools->contains('id', $school->id),
            ]);
    }

    /**
     * Assigns a permission to a user for a school
     *
     * @param School $school
     * @param string $permission
     * @param string|Model $model
     * @return User
     */
    public function givePermissionForSchool(School $school, string $permission = '*', Model|string $model = '*'): static
    {
        \Bouncer::scope()->to($school->id);
        $this->allow($permission, $model);

        return $this;
    }

    /**
     * This gets the permissions from the permission matrix
     * based on the given model. It will pull the set of
     * permissions for the model as a flattened array
     * of [permission name] => true/false
     *
     * @param string $model
     * @return array
     */
    public function getPermissions(string $model): array
    {
        $matrix = $this->getPermissionsMatrix();

        $permissions = collect($matrix['models'])
            ->first(fn ($set) => $set['model'] === $model);

        if (!$permissions) {
            return [];
        }

        return collect($permissions['permissions'])
            ->mapWithKeys(fn ($perm) => [$perm['permission'] => $perm['can']])
            ->toArray();
    }

    public function isSchoolAdmin(): bool
    {
        return $this->isA('school admin');
    }

    public function getCarbonFactory(): Factory
    {
        return new Factory([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ]);
    }

    public function getSelectionSuggestedUsers(): Collection
    {
        return static::whereHas('students', function (Builder $builder) {
                $builder->whereIn('students.uuid', $this->selectedInvoices->pluck('student_uuid'));
            })
            ->orderBy('last_name')
            ->get();
    }

    public function getPermissionsMatrix(): array
    {
        return [
            'manages_tenancy' => $this->manages_tenancy,
            'manages_school' => $this->isSchoolAdmin(),
            'roles' => [],
            'models' => [
                [
                    'model' => User::class,
                    'label' => __('Users'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', User::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', User::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', User::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', User::class),
                        ],
                        [
                            'permission' => 'edit permissions',
                            'label' => __('Edit permissions'),
                            'can' => $this->can('edit permissions', User::class),
                        ],
                    ],
                ],
                [
                    'model' => Student::class,
                    'label' => __('Students'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', Student::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', Student::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', Student::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', Student::class),
                        ],
                    ],
                ],
                [
                    'model' => FeeCategory::class,
                    'label' => __('Fee categories'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', FeeCategory::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', FeeCategory::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', FeeCategory::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', FeeCategory::class),
                        ],
                    ],
                ],
                [
                    'model' => Department::class,
                    'label' => __('Departments'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', Department::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', Department::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', Department::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', Department::class),
                        ],
                    ],
                ],
                [
                    'model' => Fee::class,
                    'label' => __('Fees'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', Fee::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', Fee::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', Fee::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', Fee::class),
                        ],
                    ],
                ],
                [
                    'model' => Scholarship::class,
                    'label' => __('Scholarships'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', Scholarship::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', Scholarship::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', Scholarship::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', Scholarship::class),
                        ],
                    ],
                ],
                [
                    'model' => Invoice::class,
                    'label' => __('Invoices'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', Invoice::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', Invoice::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', Invoice::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', Invoice::class),
                        ],
                    ],
                ],
                [
                    'model' => InvoicePayment::class,
                    'label' => __('Invoice payments'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', InvoicePayment::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', InvoicePayment::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', InvoicePayment::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', InvoicePayment::class),
                        ],
                    ],
                ],
                [
                    'model' => InvoiceImport::class,
                    'label' => __('Invoice imports'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', InvoiceImport::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', InvoiceImport::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', InvoiceImport::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', InvoiceImport::class),
                        ],
                        [
                            'permission' => 'roll back',
                            'label' => __('Roll back'),
                            'can' => $this->can('roll back', InvoiceImport::class),
                        ],
                    ],
                ],
                [
                    'model' => PaymentImport::class,
                    'label' => __('Payment imports'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', PaymentImport::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', PaymentImport::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', PaymentImport::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', PaymentImport::class),
                        ],
                        [
                            'permission' => 'roll back',
                            'label' => __('Roll back'),
                            'can' => $this->can('roll back', PaymentImport::class),
                        ],
                    ],
                ],
                [
                    'model' => InvoiceLayout::class,
                    'label' => __('Invoice layouts'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', InvoiceLayout::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', InvoiceLayout::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', InvoiceLayout::class),
                        ],
                        [
                            'permission' => 'delete',
                            'label' => __('Delete'),
                            'can' => $this->can('delete', InvoiceLayout::class),
                        ],
                    ],
                ],
                [
                    'model' => PaymentMethod::class,
                    'label' => __('Payment methods'),
                    'permissions' => [
                        [
                            'permission' => 'viewAny',
                            'label' => __('View'),
                            'can' => $this->can('viewAny', PaymentMethod::class),
                        ],
                        [
                            'permission' => 'create',
                            'label' => __('Create'),
                            'can' => $this->can('create', PaymentMethod::class),
                        ],
                        [
                            'permission' => 'update',
                            'label' => __('Update'),
                            'can' => $this->can('update', PaymentMethod::class),
                        ],
                    ],
                ],
            ],
        ];
    }
}
