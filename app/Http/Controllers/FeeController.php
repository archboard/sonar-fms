<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeeResource;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Fee::class, 'fee');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $fees = Fee::filter($request->all())
            ->with([
                'feeCategory',
                'department',
                'currency',
            ])
            ->paginate($request->input('perPage', 15));
        $title = __('Fees');

        return inertia('fees/Index', [
            'title' => $title,
            'fees' => FeeResource::collection($fees),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'description' => 'nullable',
            'amount' => 'required|integer',
            'fee_category_id' => 'nullable',
            'department_id' => 'nullable',
        ]);

        $school = $request->school();
        $data['tenant_id'] = $school->tenant_id;

        $school->fees()->create($data);

        session()->flash('success', __('Fee created successfully.'));

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Fee $fee)
    {
        $fee->load('feeCategory', 'department');
        $title = $fee->name;

        return inertia('fees/Show', [
            'title' => $title,
            'fee' => $fee->toResource(),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Fee $fee)
    {
        $data = $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'description' => 'nullable',
            'amount' => 'required|integer',
            'fee_category_id' => 'nullable',
            'department_id' => 'nullable',
        ]);

        $fee->update($data);

        session()->flash('success', __('Fee updated successfully.'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Fee $fee)
    {
        $fee->delete();

        session()->flash('success', __('Fee deleted successfully.'));

        return back();
    }
}
