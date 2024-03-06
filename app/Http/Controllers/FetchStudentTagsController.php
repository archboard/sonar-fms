<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\School;
use App\Models\Tag;
use Illuminate\Http\Request;

class FetchStudentTagsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request, School $school)
    {
        $tags = Tag::query()
            ->select('id', 'name', 'color')
            ->where('type', Tag::student($school))
            ->ordered()
            ->get();

        return TagResource::collection($tags);
    }
}
