<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class SetStudentAccountBalanceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $student->setAccountBalance()
            ->save();

        session()->flash('success', __('Account balance updated.'));

        return back();
    }
}
