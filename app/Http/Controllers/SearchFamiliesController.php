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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $this->authorize('view', Student::class);

        $families = Family::filter($request->all())
            ->orderBy('families.name')
            ->orderBy('families.id');

        return FamilyResource::collection($families);
    }
}
