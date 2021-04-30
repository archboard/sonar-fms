<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;

class FeeCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FeeCategory::class, 'fee_category');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function index()
    {
        $categories = FeeCategory::orderBy('name')
            ->get();

        return FeeCategory::resource($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required']);

        /** @var FeeCategory $category */
        $category = $request->tenant()
            ->feeCategories()
            ->create($data);

        $message = __('Category created successfully.');

        if ($request->wantsInertia()) {
            session()->flash('success', $message);
            return back();
        }

        return response()->json([
            'level' => 'success',
            'message' => $message,
            'data' => $category->toResource(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FeeCategory  $feeCategory
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(FeeCategory $feeCategory)
    {
        return $feeCategory->toResource();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FeeCategory  $feeCategory
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, FeeCategory $feeCategory)
    {
        $data = $request->validate(['name' => 'required']);

        $feeCategory->update($data);

        $message = __('Category updated successfully.');

        if ($request->wantsInertia()) {
            session()->flash('success', $message);
            return back();
        }

        return response()->json([
            'level' => 'success',
            'message' => $message,
            'data' => $feeCategory->toResource(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FeeCategory  $feeCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(FeeCategory $feeCategory)
    {
        $feeCategory->delete();

        return response()->json([
            'level' => 'success',
            'message' => __('Category deleted successfully.'),
        ]);
    }
}
