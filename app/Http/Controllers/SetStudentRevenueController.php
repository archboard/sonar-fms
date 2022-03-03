<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class SetStudentRevenueController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $student->setRevenue()
            ->save();

        session()->flash('success', __('Revenue updated.'));

        return back();
    }
}
