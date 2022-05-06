<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class FamilyController extends Controller
{
    public function show(Family $family)
    {
        $this->authorize('view', Student::class);

        $family->load('students');

        return $family->toResource();
    }

    public function store(Request $request)
    {
        $this->authorize('update', Student::class);
        $school = $request->school();

        $data = $request->validate([
            'family_id' => ['nullable', Rule::exists('families', 'id')->where('school_id', $school->id)],
            'students' => ['array', 'required'],
            'name' => ['nullable', Rule::requiredIf(fn () => is_null($request->input('family_id')))],
            'notes' => ['nullable'],
        ]);

        $familyId = $data['family_id']
            ?: $school->families()
                ->create(Arr::only($data, ['name', 'notes']))
                ->id;

        $school->students()
            ->whereIn('uuid', $data['students'])
            ->update(['family_id' => $familyId]);

        session()->flash('success', __('Family saved successfully.'));

        return back();
    }
}
