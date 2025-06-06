<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Department::class, 'department');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $departments = Department::orderBy('name')
            ->get();

        return DepartmentResource::collection($departments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        /** @var Department $department */
        $department = $request->tenant()
            ->departments()
            ->create($data);

        $message = __('Department created successfully.');

        if ($request->wantsInertia()) {
            session()->flash('success', $message);

            return back();
        }

        return response()->json([
            'level' => 'success',
            'message' => $message,
            'data' => $department->toResource(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Department $department)
    {
        return $department->toResource();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $department->update($data);

        $message = __('Department updated successfully.');

        if ($request->wantsInertia()) {
            session()->flash('success', $message);

            return back();
        }

        return response()->json([
            'level' => 'success',
            'message' => __('Department updated successfully.'),
            'data' => $department->toResource(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json([
            'level' => 'success',
            'message' => __('Department deleted successfully.'),
        ]);
    }
}
