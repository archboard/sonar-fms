<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceTemplateResource;
use App\Models\InvoiceTemplate;
use App\Models\School;
use Illuminate\Http\Request;

class InvoiceTemplateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceTemplate::class, 'template');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, School $school)
    {
        $templates = $school->invoiceTemplates()
            ->where('for_import', $request->has('for_import'))
            ->with('user')
            ->orderBy('name')
            ->get();

        return InvoiceTemplateResource::collection($templates);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'template' => 'required|array',
            'for_import' => 'required|boolean',
        ]);

        $data['user_uuid'] = $request->user()->id;

        /** @var InvoiceTemplate $template */
        $template = $request->school()
            ->invoiceTemplates()
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
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(InvoiceTemplate $template)
    {
        return $template->load('user')
            ->toResource();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, InvoiceTemplate $template)
    {
        $data = $request->validate([
            'name' => 'required',
            'template' => 'required|array',
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(InvoiceTemplate $template)
    {
        $template->delete();

        return response()->json([
            'level' => 'success',
            'message' => __('Template deleted successfully.'),
        ]);
    }
}
