<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
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
}
