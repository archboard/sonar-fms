<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Traits\SendsApiResponses;

class FamilyStudentController extends Controller
{
    use SendsApiResponses;

    public function store(string $family, string $student)
    {
        $this->authorize('update', Student::class);

        Student::where('uuid', $student)
            ->update(['family_id' => $family]);

        return $this->success(__('Student added successfully.'));
    }

    public function destroy(string $family, string $student)
    {
        $this->authorize('update', Student::class);

        Student::where('uuid', $student)
            ->update(['family_id' => null]);

        return $this->success(__('Student removed successfully.'));
    }
}
