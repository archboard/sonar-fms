<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Student;
use App\Models\Tag;
use Illuminate\Http\Request;

class StudentTagController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorize('viewAny', $student);

        $tags = $student->tags()
            ->select('id', 'name', 'color')
            ->ordered()
            ->get();

        return TagResource::collection($tags);
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
