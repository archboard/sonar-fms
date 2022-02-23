<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use App\Models\Tag;

class FetchStudentTagsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, School $school)
    {
        $tags = Tag::query()
            ->select('name')
            ->where('type', Tag::student($school))
            ->ordered()
            ->get()
            ->map
            ->name;

        return response()->json($tags);
    }
}
