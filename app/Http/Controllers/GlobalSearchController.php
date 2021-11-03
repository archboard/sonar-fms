<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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
    public function __invoke(Request $request)
    {
        $results = (new Search())
            ->registerModel(Invoice::class, function (ModelSearchAspect $aspect) {
                $aspect->addSearchableAttribute('invoice_number')
                    ->addSearchableAttribute('title')
                    ->with('student');
            })
            ->registerModel(Student::class, 'first_name', 'last_name', 'student_number')
            ->limitAspectResults(10)
            ->search($request->input('s'))
            ->map(function (SearchResult $result) {
                $result->searchable = $result->searchable->toResource();

                return $result;
            });

        ray($results);

        return response()
            ->json($results->groupByType());
    }
}
