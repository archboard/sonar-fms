<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Searchable\ModelSearchAspect;
use Spatie\Searchable\Search;
use Spatie\Searchable\SearchResult;

class GlobalSearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, School $school)
    {
        /** @var User $user */
        $user = $request->user();
        $search = new Search;

        if ($user->can('view', Invoice::class) || $user->students->isNotEmpty()) {
            $search->registerModel(Invoice::class, function (ModelSearchAspect $aspect) use ($school, $user) {
                $aspect->addSearchableAttribute('invoice_number')
                    ->addSearchableAttribute('title')
                    ->where('school_id', $school->id)
                    ->with('student', 'students', 'currency');

                if ($user->cant('view', Invoice::class)) {
                    $aspect->forUser($user)
                        ->published();
                }
            });
        }

        if ($user->can('view', Student::class) || $user->students->isNotEmpty()) {
            $search->registerModel(Student::class, function (ModelSearchAspect $aspect) use ($school, $user) {
                $aspect->addSearchableAttribute('first_name')
                    ->addSearchableAttribute('last_name')
                    ->addSearchableAttribute('student_number')
                    ->where('school_id', $school->id)
                    ->orderBy('enrolled', 'desc')
                    ->orderBy('last_name')
                    ->orderBy('first_name');

                if ($user->cant('view', Student::class)) {
                    $aspect->whereIn('students.uuid', $user->students->pluck('uuid'));
                }
            });
        }

        if ($user->can('view', Fee::class)) {
            $search->registerModel(Fee::class, function (ModelSearchAspect $aspect) {
                $aspect->addSearchableAttribute('name')
                    ->with('currency');
            });
        }

        if ($user->can('view', Scholarship::class)) {
            $search->registerModel(Scholarship::class, function (ModelSearchAspect $aspect) use ($school) {
                $aspect->addSearchableAttribute('name')
                    ->where('school_id', $school->id)
                    ->with('currency');
            });
        }

        $results = $search->limitAspectResults(10)
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
