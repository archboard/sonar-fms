<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use GrantHolle\PowerSchool\Auth\Traits\AuthenticatesUsingPowerSchoolWithOpenId;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PowerSchoolOpenIdLoginController extends Controller
{
    use AuthenticatesUsingPowerSchoolWithOpenId;

    /**
     * The user has been authenticated.
     *
     * @return mixed
     */
    protected function authenticated(Request $request, User $user, Collection $data)
    {
        $adminSchools = $data->get('adminSchools', []);

        if (! empty($adminSchools)) {
            $schools = School::whereIn('school_number', $adminSchools)
                ->pluck('id');
            $user->schools()->syncWithoutDetaching($schools);
            $user->assign('staff');
        }

        if ($schoolId = $data->get('schoolID')) {
            $school = School::where('school_number', $schoolId)
                ->first();
            $user->school_id = $school->id;
        }

        if ($data->get('usertype') === 'guardian') {
            // Get the contact id if there isn't one set
            $user->setContactId()
                ->syncStudents()
                ->setSchool()
                ->assign('contact');
        }

        $user->save();
    }

    /**
     * Gets the default attributes to be added for this user
     */
    protected function getDefaultAttributes(Request $request, Collection $data): array
    {
        return [
            'tenant_id' => $request->tenant()->id,
        ];
    }
}
