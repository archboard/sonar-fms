<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class SyncStudentGuardiansController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->syncContacts();

        session()->flash('success', __('Guardians synced successfully.'));

        return back();
    }
}
