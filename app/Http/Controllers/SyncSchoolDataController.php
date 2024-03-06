<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSchool;
use App\Models\School;
use Illuminate\Http\Request;

class SyncSchoolDataController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, School $school)
    {
        $this->dispatch(new SyncSchool($school, true, $request->user()));

        session()->flash('success', __('School sync has started.'));

        return back();
    }
}
