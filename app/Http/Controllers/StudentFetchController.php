<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentFetchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $students = $request->school()
            ->students()
            ->filter($request->all())
            ->limit(10)
            ->get();

        return StudentResource::collection($students);
    }
}
