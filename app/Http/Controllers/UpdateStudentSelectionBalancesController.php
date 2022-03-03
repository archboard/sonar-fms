<?php

namespace App\Http\Controllers;

use App\Jobs\SetStudentCachedValues;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class UpdateStudentSelectionBalancesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $this->authorize('update', Student::class);

        /** @var User $user */
        $user = $request->user();

        $user->studentSelections()->pluck('student_uuid')
            ->each(fn ($uuid) => SetStudentCachedValues::dispatch($uuid));

        session()->flash('success', __('Balance update started successfully.'));

        return back();
    }
}
