<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceLayoutResource;
use App\Models\InvoiceLayout;
use App\Rules\InvoiceLayoutData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('layouts/Index', [
            'title' => $title,
            'layouts' => InvoiceLayoutResource::collection($layouts),
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create()
    {
        $title = __('Create a new invoice layout');

        return inertia('layouts/Create', [
            'title' => $title,
            'method' => 'post',
            'endpoint' => route('layouts.store'),
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
            'paper_size' => Rule::in(['A4', 'Letter']),
            'layout_data' => ['required', new InvoiceLayoutData],
            'layout_data.rows' => ['required', 'array'],
        ]);

        $school = $request->school();

        $data['tenant_id'] = $school->tenant_id;
        // If a default layout doesn't exist, set it to be this one
        $data['is_default'] = $school->invoiceLayouts()
            ->default()
            ->doesntExist();
        /** @var InvoiceLayout $layout */
        $layout = $school->invoiceLayouts()
            ->create($data);

        session()->flash('success', __('Invoice layout created successfully.'));

        return $this->afterSave($request, $layout);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceLayout  $layout
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(InvoiceLayout $layout)
    {
        return $layout->toResource();
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
            'method' => 'put',
            'endpoint' => route('layouts.update', $layout),
            'preview' => route('layouts.preview', $layout),
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
            'paper_size' => Rule::in(['A4', 'Letter']),
            'layout_data' => ['required', new InvoiceLayoutData],
            'layout_data.rows' => ['required', 'array'],
        ]);

        $layout->update($data);

        session()->flash('success', __('Invoice layout updated successfully.'));

        return $this->afterSave($request, $layout);
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

    protected function afterSave(Request $request, InvoiceLayout $layout): RedirectResponse
    {
        if ($request->input('preview')) {
            return redirect()->route('layouts.edit', $layout);
        }

        return redirect()->route('layouts.index');
    }
}
