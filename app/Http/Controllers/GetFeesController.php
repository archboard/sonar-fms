<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeeResource;
use Illuminate\Http\Request;

class GetFeesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $school = $request->school();

        $fees = $school->fees()
            ->with('school', 'school.currency')
            ->orderBy('fees.name')
            ->get();

        return FeeResource::collection($fees);
    }
}
