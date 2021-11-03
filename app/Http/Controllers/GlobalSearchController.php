<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Scholarship;
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
                    ->with('student', 'students', 'currency');
            })
            ->registerModel(Student::class, function (ModelSearchAspect $aspect) {
                $aspect->addSearchableAttribute('first_name')
                    ->addSearchableAttribute('last_name')
                    ->addSearchableAttribute('student_number')
                    ->orderBy('last_name')
                    ->orderBy('first_name');
            })
            ->registerModel(Fee::class, function (ModelSearchAspect $aspect) {
                $aspect->addSearchableAttribute('name')
                    ->with('currency');
            })
            ->registerModel(Scholarship::class, function (ModelSearchAspect $aspect) {
                $aspect->addSearchableAttribute('name')
                    ->with('currency');
            })
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
