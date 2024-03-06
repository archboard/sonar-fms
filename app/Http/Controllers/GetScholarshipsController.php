<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScholarshipResource;
use Illuminate\Http\Request;

class GetScholarshipsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $scholarships = $request->school()
            ->scholarships()
            ->filter([])
            ->get();

        return ScholarshipResource::collection($scholarships);
    }
}
