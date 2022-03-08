<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class MyStudentController extends Controller
{
    public function index(Request $request)
    {
        $title = __('My students');
        /** @var User $user */
        $user = $request->user();

        $students = $user->getMyStudents();

        return inertia('my-students/Index', [
            'title' => $title,
            'students' => StudentResource::collection($students),
        ])->withViewData(compact('title'));
    }

    public function show(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $title = $student->full_name;
        $student->load('currency');
        $invoiceCount = $student->invoices()
            ->unpaid()
            ->published()
            ->isNotVoid()
            ->count();

        /** @var User $user */
        $user = $request->user();

        // Force the view permission for this student
        $invoicePermissions = $user->getPermissions(Invoice::class);
        $invoicePermissions['view'] = true;

        return inertia('my-students/Show', [
            'title' => $title,
            'invoiceCount' => $invoiceCount,
            'student' => $student->toResource(),
            'permissions' => [
                'invoices' => $invoicePermissions,
            ],
        ])->withViewData(compact('title'));
    }
}
