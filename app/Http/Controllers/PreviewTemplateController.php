<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;

class PreviewTemplateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, School $school)
    {
        $data = $request->validate(['template' => 'string|required']);

        /** @var Student $student */
        $student = $school->students()
            ->inRandomOrder()
            ->first();
        $compiled = $school->compileTemplate($data['template'], $request->user(), $student);

        return response()->json(compact('compiled'));
    }
}
