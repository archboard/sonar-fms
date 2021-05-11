<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScholarshipResource;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScholarshipController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Scholarship::class, 'scholarship');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Scholarships');
        $scholarships = $request->school()
            ->scholarships()
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('scholarships/Index', [
            'title' => $title,
            'scholarships' => ScholarshipResource::collection($scholarships),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'percentage' => 'nullable|numeric|required_without:amount|max:100',
            'amount' => 'nullable|integer|required_without:percentage',
            'resolution_strategy' => [
                'nullable',
                'required_with:amount,percentage',
                Rule::in(array_keys(Scholarship::getResolutionStrategies())),
            ],
        ]);

        $school = $request->school();
        $data['tenant_id'] = $school->tenant_id;

        $school->scholarships()->create($data);

        session()->flash('success', __('Scholarship created successfully.'));

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Scholarship  $scholarship
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Scholarship $scholarship)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Scholarship  $scholarship
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Scholarship $scholarship)
    {
        $data = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'percentage' => 'nullable|numeric|required_without:amount|max:100',
            'amount' => 'nullable|integer|required_without:percentage',
            'resolution_strategy' => [
                'nullable',
                'required_with:amount,percentage',
                Rule::in(array_keys(Scholarship::getResolutionStrategies())),
            ],
        ]);

        $scholarship->update($data);

        session()->flash('success', __('Scholarship updated successfully.'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Scholarship  $scholarship
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();

        session()->flash('success', __('Scholarship deleted successfully.'));

        return back();
    }
}
