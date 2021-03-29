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

    /**
     * Displays all the students based on a filter
     *
     * @param Request $request
     * @param School $school
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request, School $school)
    {
        $title = __('Students');
        $request->user()->load('studentSelections');

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

    /**
     * Displays the student
     *
     * @param Request $request
     * @param Student $student
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, Student $student)
    {
        $title = $student->full_name;
        $student->load('users');

        return inertia('students/Show', [
            'title' => $title,
            'student' => $student->toResource(),
        ])->withViewData(compact('title'));
    }
}
