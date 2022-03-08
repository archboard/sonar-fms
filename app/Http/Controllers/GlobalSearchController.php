<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Spatie\Searchable\ModelSearchAspect;
use Spatie\Searchable\Search;
use Spatie\Searchable\SearchResult;

class GlobalSearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, School $school)
    {
        $results = (new Search())
            ->registerModel(Invoice::class, function (ModelSearchAspect $aspect) use ($school) {
                $aspect->addSearchableAttribute('invoice_number')
                    ->addSearchableAttribute('title')
                    ->where('school_id', $school->id)
                    ->with('student', 'students', 'currency');
            })
            ->registerModel(Student::class, function (ModelSearchAspect $aspect) use ($school) {
                $aspect->addSearchableAttribute('first_name')
                    ->addSearchableAttribute('last_name')
                    ->addSearchableAttribute('student_number')
                    ->where('school_id', $school->id)
                    ->orderBy('enrolled', 'desc')
                    ->orderBy('last_name')
                    ->orderBy('first_name');
            })
            ->registerModel(Fee::class, function (ModelSearchAspect $aspect) use ($school) {
                $aspect->addSearchableAttribute('name')
                    ->with('currency');
            })
            ->registerModel(Scholarship::class, function (ModelSearchAspect $aspect) use ($school) {
                $aspect->addSearchableAttribute('name')
                    ->where('school_id', $school->id)
                    ->with('currency');
            })
            ->limitAspectResults(10)
            ->search($request->input('s'))
            ->map(function (SearchResult $result) {
                // Each result should have the HasResource trait
                $result->searchable = $result->searchable->toResource(); // @phpstan-ignore-line

                return $result;
            });

        return response()
            ->json($results->groupByType()); // @phpstan-ignore-line
    }
}
