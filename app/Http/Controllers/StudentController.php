<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request, School $school)
    {
        $title = __('Students');

        return inertia('students/Index', [
            'title' => $title,
        ])->withViewData(compact('title'));
    }
}
