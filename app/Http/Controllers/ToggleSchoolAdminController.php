<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Bouncer;

class ToggleSchoolAdminController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, School $school, User $user)
    {
        $this->authorize('toggle school admin');

        Bouncer::allow('school admin')->everything();
        $action = $user->isA('school admin')
            ? 'retract'
            : 'assign';

        $user->$action('school admin');

        Bouncer::refreshFor($user);

        $message = $user->isA('school admin')
            ? __(':name can now manage :school.', ['name' => $user->full_name, 'school' => $school->name])
            : __(':name does not manage :school now.', ['name' => $user->full_name, 'school' => $school->name]);

        return response()->json([
            'level' => 'success',
            'message' => $message,
        ]);
    }
}
