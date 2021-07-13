<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLayout;
use Illuminate\Http\Request;

class InvoiceLayoutController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceLayout::class, 'layout');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Invoice layouts');
        $layouts = $request->school()
            ->invoiceLayouts()
            ->orderBy('name')
            ->get();

        return inertia('layouts/Index', [
            'title' => $title,
            'layouts' => $layouts,
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create()
    {
        $title = __('Create new invoice layout');

        return inertia('layouts/Create', [
            'title' => $title,
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
            'name' => 'required|max:255',
            'locale' => 'nullable',
            'data' => 'required|array',
        ]);

        $school = $request->school();

        $data['tenant_id'] = $school->tenant_id;
        $school->invoiceLayouts()
            ->create($data);

        session()->flash('success', __('Invoice layout created successfully.'));

        return redirect()->route('layouts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceLayout  $layout
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceLayout $layout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceLayout  $layout
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(InvoiceLayout $layout)
    {
        $title = __('Edit :name', ['name' => $layout->name]);

        return inertia('layouts/Create', [
            'title' => $title,
            'layout' => $layout->toResource(),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceLayout  $layout
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, InvoiceLayout $layout)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'locale' => 'nullable',
            'data' => 'required|array',
        ]);

        $layout->update($data);

        session()->flash('success', __('Invoice layout updated successfully.'));

        return redirect()->route('layouts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceLayout  $layout
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(InvoiceLayout $layout)
    {
        $layout->delete();

        session()->flash('success', __('Invoice layout deleted successfully.'));

        return redirect()->route('layouts.index');
    }
}
