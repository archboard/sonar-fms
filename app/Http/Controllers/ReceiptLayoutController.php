<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReceiptLayoutResource;
use App\Models\ReceiptLayout;
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReceiptLayout  $receiptLayout
     * @return \Illuminate\Http\Response
     */
    public function show(ReceiptLayout $receiptLayout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceiptLayout  $receiptLayout
     * @return \Illuminate\Http\Response
     */
    public function edit(ReceiptLayout $receiptLayout)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReceiptLayout  $receiptLayout
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReceiptLayout $receiptLayout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceiptLayout  $receiptLayout
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReceiptLayout $receiptLayout)
    {
        //
    }
}
