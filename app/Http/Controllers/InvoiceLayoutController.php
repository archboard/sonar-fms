<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveLayoutRequest;
use App\Http\Resources\InvoiceLayoutResource;
use App\Models\InvoiceLayout;
use Illuminate\Http\RedirectResponse;
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
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('layouts/Index', [
            'title' => $title,
            'layouts' => InvoiceLayoutResource::collection($layouts),
            'permissions' => [
                'layouts' => $request->user()->getPermissions(InvoiceLayout::class),
            ],
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
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Invoice layouts'), route('layouts.index')),
            $this->makeBreadcrumb(__('Create layout'), route('layouts.create')),
        ];

        return inertia('layouts/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'method' => 'post',
            'endpoint' => route('layouts.store'),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SaveLayoutRequest $request
     * @return RedirectResponse
     */
    public function store(SaveLayoutRequest $request)
    {
        $layout = InvoiceLayout::saveFromRequest($request);

        session()->flash('success', __('Layout created successfully.'));

        return $this->afterSave($request, $layout);
    }

    /**
     * Display the specified resource.
     *
     * @param InvoiceLayout $layout
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(InvoiceLayout $layout)
    {
        return $layout->toResource();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param InvoiceLayout $layout
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(InvoiceLayout $layout)
    {
        $title = __('Edit :name', ['name' => $layout->name]);
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Invoice layouts'), route('layouts.index')),
            $this->makeBreadcrumb(__('Edit layout'), route('layouts.edit', $layout)),
        ];

        return inertia('layouts/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'layout' => $layout->toResource(),
            'method' => 'put',
            'endpoint' => route('layouts.update', $layout),
            'preview' => route('layouts.preview', $layout),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SaveLayoutRequest $request
     * @param InvoiceLayout $layout
     * @return RedirectResponse
     */
    public function update(SaveLayoutRequest $request, InvoiceLayout $layout)
    {
        $layout->update($request->validated());

        session()->flash('success', __('Layout updated successfully.'));

        return $this->afterSave($request, $layout);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param InvoiceLayout $layout
     * @return RedirectResponse
     */
    public function destroy(InvoiceLayout $layout)
    {
        $layout->delete();

        session()->flash('success', __('Layout deleted successfully.'));

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
