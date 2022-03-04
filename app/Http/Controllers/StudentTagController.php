<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Student;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StudentTagController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $tags = $student->tags()
            ->select('id', 'name', 'color')
            ->ordered()
            ->get();

        return TagResource::collection($tags);
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $data = $request->validate([
            'tags' => ['array'],
            'tags.*.name' => ['required', 'string', 'max:255'],
            'tags.*.color' => ['required', 'string'],
        ]);
        $tagsByName = Arr::keyBy($data['tags'], 'name');

        // Sync the student's tags and update the color
        $student->syncTagsWithType(array_keys($tagsByName), Tag::student($student->school))
            ->tags
            ->each(function (Tag $tag) use ($tagsByName) {
                $tag->update(['color' => $tagsByName[$tag->name]['color']]);
            });

        session()->flash('success', __('Tags saved successfully.'));

        return back();
    }
}
