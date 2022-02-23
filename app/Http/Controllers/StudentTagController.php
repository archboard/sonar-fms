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
        $this->authorize('viewAny', $student);

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
        $this->authorize('update', $student);

        $data = $request->validate(['tags' => ['array']]);

        $student->syncTagsWithType($data['tags'], Tag::student($student->school));

        session()->flash('success', __('Tags saved successfully.'));

        return back();
    }
}
