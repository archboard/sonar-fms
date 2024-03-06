<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSelectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return StudentResource::collection(
            $request->user()->studentSelections
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, School $school)
    {
        /** @var User $user */
        $user = $request->user();

        $selection = $school->students()
            ->filter($request->all())
            ->pluck('uuid')
            ->map(fn ($student) => [
                'school_id' => $school->id,
                'student_uuid' => $student,
                'user_uuid' => $user->id,
            ]);

        // Delete the existing selection
        $user->studentSelections()->delete();

        if ($selection->isNotEmpty()) {
            DB::table('student_selections')->insert($selection->toArray());
        }

        session()->flash('success', __('Selected :count students', ['count' => $selection->count()]));

        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        /** @var User $user */
        $user = $request->user();

        $user->selectStudent($id);

        return response()->json();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $studentId)
    {
        /** @var User $user */
        $user = $request->user();

        $user->studentSelections()
            ->student($studentId)
            ->delete();

        return response()->json();
    }
}
