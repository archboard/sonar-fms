<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
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

        $students = $school->students()
            ->filter($request->all())
            ->paginate($request->input('perPage', 25));

        // This query needs to have some way to limit by
        // who has outstanding balances

        return inertia('students/Index', [
            'title' => $title,
            'students' => StudentResource::collection($students),
        ])->withViewData(compact('title'));
    }
}
