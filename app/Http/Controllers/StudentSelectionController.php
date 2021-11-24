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
     * @param Request $request
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
     * @param \Illuminate\Http\Request $request
     * @param School $school
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        /** @var User $user */
        $user = $request->user();

        $user->selectStudent($id);

        return response()->json();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $studentId)
    {
        /** @var User $user */
        $user = $request->user();

        $user->studentSelections()
            ->student($studentId)
            ->delete();

        return response()->json();
    }
}
