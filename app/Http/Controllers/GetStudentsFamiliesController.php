<?php

namespace App\Http\Controllers;

use App\Http\Resources\FamilyResource;
use App\Models\Family;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetStudentsFamiliesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate(['students' => ['array', 'required']]);

        $families = Family::whereHas('students', function (Builder $builder) use ($data) {
                $builder->whereIn('students.uuid', $data['students']);
            })
            ->get();

        return FamilyResource::collection($families);
    }
}
