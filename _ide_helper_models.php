<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\School
 *
 * @mixin IdeHelperSchool
 * @property int $id
 * @property int $tenant_id
 * @property int $dcid
 * @property int $school_number
 * @property string $name
 * @property int $high_grade
 * @property int $low_grade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|School newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|School newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|School query()
 * @method static \Illuminate\Database\Eloquent\Builder|School whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereDcid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereHighGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereLowGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereSchoolNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|School whereUpdatedAt($value)
 */
	class IdeHelperSchool extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Tenant
 *
 * @mixin IdeHelperTenant
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string|null $ps_url
 * @property string|null $ps_client_id
 * @property string|null $ps_secret
 * @property bool $allow_password_auth
 * @property string|null $subscription_started_at
 * @property string|null $subscription_expires_at
 * @property string $license
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\School[] $schools
 * @property-read int|null $schools_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Spatie\Multitenancy\TenantCollection|static[] all($columns = ['*'])
 * @method static \Spatie\Multitenancy\TenantCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereAllowPasswordAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
 */
	class IdeHelperTenant extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property int $tenant_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $password
 * @property int|null $school_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Silber\Bouncer\Database\Ability[] $abilities
 * @property-read int|null $abilities_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Silber\Bouncer\Database\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\School|null $school
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\School[] $schools
 * @property-read int|null $schools_count
 * @property-read \App\Models\Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIs($role)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAll($role)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsNot($role)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class IdeHelperUser extends \Eloquent {}
}

