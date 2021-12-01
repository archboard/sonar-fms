<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentImportTemplateResource;
use App\Models\PaymentImport;
use App\Models\PaymentImportTemplate;
use App\Models\School;
use Illuminate\Http\Request;

class PaymentImportTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(School $school)
    {
        $templates = $school->paymentImportTemplates()
            ->orderBy('name')
            ->with('user')
            ->get();

        return PaymentImportTemplateResource::collection($templates);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, School $school)
    {
        $this->authorize('create', PaymentImport::class);

        $data = $request->validate([
            'name' => ['required'],
            'template' => ['array', 'required'],
        ]);

        $data['school_id'] = $school->id;

        $template = $request->user()
            ->paymentImportTemplates()
            ->create($data);

        return response()->json([
            'level' => 'success',
            'message' => __('Template created successfully.'),
            'data' => $template
                ->load('user')
                ->toResource(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentImportTemplate  $template
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(PaymentImportTemplate $template)
    {
        return $template->load('user')
            ->toResource();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentImportTemplate  $template
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, PaymentImportTemplate $template)
    {
        $this->authorize('create', PaymentImport::class);

        $data = $request->validate([
            'name' => ['required'],
            'template' => ['array', 'required'],
        ]);

        $template->update($data);

        return response()->json([
            'level' => 'success',
            'message' => __('Template updated successfully.'),
            'data' => $template
                ->load('user')
                ->toResource(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentImportTemplate  $template
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PaymentImportTemplate $template)
    {
        $template->delete();

        return response()->json([
            'level' => 'success',
            'message' => __('Template deleted successfully.'),
        ]);
    }
}
