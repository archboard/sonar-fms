<?php

namespace App\Http\Controllers;

use App\Http\Resources\FamilyResource;
use App\Models\Family;
use App\Models\Student;
use Illuminate\Http\Request;

class SearchFamiliesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $this->authorize('view', Student::class);

        $families = Family::filter($request->all())
            ->where('school_id', $request->school()->id)
            ->orderBy('families.name')
            ->orderBy('families.id')
            ->get();

        return FamilyResource::collection($families);
    }
}
