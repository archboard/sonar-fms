<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\Tag;
use Illuminate\Http\Request;

class StudentTagController extends Controller
{
    public function index(Request $request, School $school, Student $student)
    {
        $this->authorize('viewAny', Student::class);

        $tags = $student->tags()
            ->select('name')
            ->ordered()
            ->get()
            ->map
            ->name;

        return response()->json($tags);
    }

    public function store(Request $request, Student $student)
    {
        $data = $request->validate(['tags' => ['array']]);
    }
}
