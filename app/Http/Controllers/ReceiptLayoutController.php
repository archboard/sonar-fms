<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveLayoutRequest;
use App\Http\Resources\ReceiptLayoutResource;
use App\Models\ReceiptLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReceiptLayoutController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ReceiptLayout::class, 'layout');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Receipt layouts');
        $layouts = $request->school()
            ->receiptLayouts()
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('layouts/receipts/Index', [
            'title' => $title,
            'layouts' => ReceiptLayoutResource::collection($layouts),
            'permissions' => [
                'layouts' => $request->user()->getPermissions(ReceiptLayout::class),
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
        $title = __('Create a new receipt layout');
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Receipt layouts'), route('receipt-layouts.index')),
            $this->makeBreadcrumb(__('Create layout'), route('receipt-layouts.create')),
        ];

        return inertia('layouts/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'method' => 'post',
            'endpoint' => route('receipt-layouts.store'),
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
        $layout = ReceiptLayout::saveFromRequest($request);

        session()->flash('success', __('Invoice layout created successfully.'));

        return $this->afterSave($request, $layout);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReceiptLayout $layout
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(ReceiptLayout $layout)
    {
        return $layout->toResource();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceiptLayout $layout
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(ReceiptLayout $layout)
    {
        $title = __('Edit :name', ['name' => $layout->name]);
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Receipt layouts'), route('receipt-layouts.index')),
            $this->makeBreadcrumb(__('Edit layout'), route('receipt-layouts.edit', $layout)),
        ];

        return inertia('layouts/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'layout' => $layout->toResource(),
            'method' => 'put',
            'endpoint' => route('receipt-layouts.update', $layout),
            'preview' => route('receipt-layouts.preview', $layout),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SaveLayoutRequest $request
     * @param \App\Models\ReceiptLayout $layout
     * @return \Illuminate\Http\Response
     */
    public function update(SaveLayoutRequest $request, ReceiptLayout $layout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceiptLayout $layout
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReceiptLayout $layout)
    {
        //
    }

    protected function afterSave(Request $request, ReceiptLayout $layout): RedirectResponse
    {
        if ($request->input('preview')) {
            return redirect()->route('receipt-layouts.edit', $layout);
        }

        return redirect()->route('receipt-layouts.index');
    }
}
