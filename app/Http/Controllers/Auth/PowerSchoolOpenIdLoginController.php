<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use GrantHolle\PowerSchool\Auth\Traits\AuthenticatesUsingPowerSchoolWithOpenId;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PowerSchoolOpenIdLoginController extends Controller
{
    use AuthenticatesUsingPowerSchoolWithOpenId;

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @param \Illuminate\Support\Collection $data
     * @return mixed
     */
    protected function authenticated(Request $request, User $user, Collection $data)
    {
        $adminSchools = $data->get('adminSchools', []);

        if (!empty($adminSchools)) {
            $schools = School::whereIn('school_number', $adminSchools)
                ->pluck('id');
            $user->schools()->syncWithoutDetaching($schools);
        }

        if ($schoolId = $data->get('schoolID')) {
            $school = School::where('school_number', $schoolId)
                ->first();
            $user->update(['school_id' => $school->id]);
        }

        if ($data->get('usertype') === 'guardian') {
            // Get the contact id if there isn't one set
            if (!$user->contact_id) {
                $response = PowerSchool::pq('com.archboard.sonarfms.guardian.contactid', ['guardianid' => $user->guardian_id]);

                if (isset($response->record) && count($response->record) === 1) {
                    $user->update(['contact_id' => $response->record[0]->personid]);
                }
            }

            // Sync the contact's students
            $students = Student::whereIn('sis_id', $data->get('studentids', []))
                ->pluck('id')
                ->map(fn ($student) => [
                    'student_id' => $student,
                    'user_id' => $user->id,
                ]);

            $user->students()->detach();
            DB::table('student_user')->insert($students->toArray());
        }
    }
}
