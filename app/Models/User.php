<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Factory;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Silber\Bouncer\Database\HasRolesAndAbilities;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasResource;
    use HasRolesAndAbilities;
    use BelongsToTenant;

    const TEACHER = 'teacher';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'manages_tenancy' => 'boolean',
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
            return $this->studentSelections->pluck('student_id');
        }

        return collect();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDateFactoryAttribute()
    {
        return new Factory([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ]);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->where('active', true)
            ->withPivot(['staff_id']);
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
                $join->on('student_selections.user_id', '=', 'users.id');
            })
            ->whereRaw('student_selections.school_id = users.school_id');
    }

    public function invoiceImports(): HasMany
    {
        return $this->hasMany(InvoiceImport::class);
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
    public function selectStudent(Student|int $studentId): User
    {
        if ($studentId instanceof Student) {
            $studentId = $studentId->id;
        }

        $exists = $this->studentSelections()
            ->student($studentId)
            ->exists();

        if (!$exists) {
            DB::table('student_selections')->insert([
                'school_id' => $this->school_id,
                'student_id' => $studentId,
                'user_id' => $this->id,
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
        ray($contactStudents)->purple();

        $students = Student::whereIn('sis_id', $contactStudents->pluck('dcid'))
            ->get()
            ->keyBy('sis_id');
        $schools = School::whereIn('school_number', $contactStudents->pluck('schoolNumber'))
            ->get()
            ->keyBy('school_number');

        // Attach the school relationship
        $this->schools()->syncWithoutDetaching($schools->pluck('id'));

        // TODO: finish
        $contactStudents->reduce(function ($studentUsers, $student) use ($schools, $students) {
            $school = $schools->get($students->schoolNumber);

            if (!$school) {
                return $studentUsers;
            }

            if ($existingStudent = $students->get($student->dcid)) {
                $existingStudent->update([
                    'first_name' => $student->firstName,
                    'last_name' => $student->lastName,
                    'student_number' => $student->studentNumber,
                    'school_id' => $school->id,
                ]);

                $studentUsers[] = $existingStudent->id;
                return $studentUsers;
            }

            // Create the student here

            return $studentUsers;
        }, []);

//        $students = Student::whereIn('sis_id', $data->get('studentids', []))
//            ->pluck('id')
//            ->map(fn ($student) => [
//                'student_id' => $student,
//                'user_id' => $this->id,
//            ]);
//
//        $user->students()->detach();
//        DB::table('student_user')->insert($students->toArray());
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
     * Assigns a permission to a user for a school
     *
     * @param School $school
     * @param string $permission
     * @param string|Model $model
     * @return User
     */
    public function givePermissionForSchool(School $school, string $permission = '*', $model = '*'): static
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

    public function getPermissionsMatrix(): array
    {
        return [
            'manages_tenancy' => $this->manages_tenancy,
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
                    'label' => __('Fee Categories'),
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
                    'model' => InvoiceImport::class,
                    'label' => __('Invoice Imports'),
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
                    ],
                ],
            ],
        ];
    }
}
