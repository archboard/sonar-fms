<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, School $school)
    {
        $title = __('Dashboard');
        /** @var User $user */
        $user = $request->user();

        $myStudents = $user->getMyStudents();
        $newStudents = $user->can('view', Student::class)
            ? $school->students()
                ->latest()
                ->with('currency')
                ->limit(10)
                ->get()
            : collect();

        return inertia('Index', [
            'title' => $title,
            'myStudents' => StudentResource::collection($myStudents),
            'newStudents' => StudentResource::collection($newStudents),
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
            ],
        ])->withViewData(compact('title'));
    }
}
