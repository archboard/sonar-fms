<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentImportResource;
use App\Models\PaymentImport;
use App\Models\School;
use Illuminate\Http\Request;

class PaymentImportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PaymentImport::class, 'import');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request, School $school)
    {
        $title = __('Payment imports');
        $imports = $school->paymentImports()
            ->orderBy('created_at', 'desc')
            ->filter($request->all())
            ->paginate($request->input('perPage', 15));

        return inertia('payments/imports/Index', [
            'title' => $title,
            'imports' => PaymentImportResource::collection($imports),
            'permissions' => $request->user()->getPermissions(PaymentImport::class),
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentImport $paymentImport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentImport  $paymentImport
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentImport $paymentImport)
    {
        //
    }
}
