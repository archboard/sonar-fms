<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\School;
use Illuminate\Http\Request;
use App\Models\Tag;

class FetchStudentTagsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, School $school)
    {
        $tags = Tag::query()
            ->select('name', 'color')
            ->where('type', Tag::student($school))
            ->ordered()
            ->get();

        return TagResource::collection($tags);
    }
}
