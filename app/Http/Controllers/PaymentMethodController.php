<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentMethodDriverResource;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Rules\PaymentMethodDriverOptions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PaymentMethod::class, 'payment_method');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Payment Methods');
        $breadcrumbs = [
            [
                'label' => __('School Settings'),
                'route' => route('settings.school'),
            ],
            [
                'label' => $title,
                'route' => route('payment-methods.index'),
            ],
        ];
        $drivers = PaymentMethod::getListForSchool($request->school());

        return inertia('payment-methods/Index', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'paymentMethods' => PaymentMethodDriverResource::collection($drivers),
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form to create a new payment method
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create(Request $request)
    {
        $title = __('Add new payment method');
        $breadcrumbs = [
            [
                'label' => __('School Settings'),
                'route' => route('settings.school'),
            ],
            [
                'label' => __('Payment Methods'),
                'route' => route('payment-methods.index'),
            ],
            [
                'label' => $title,
                'route' => route('payment-methods.create'),
            ],
        ];
        $drivers = PaymentMethodDriverResource::collection(
            PaymentMethod::getAllDrivers()
        );

        return inertia('payment-methods/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'drivers' => $drivers,
            'driver' => $request->input('driver'),
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
            'driver' => [
                'required',
                Rule::in(array_keys(PaymentMethod::drivers())),
                Rule::unique('payment_methods')->where(function ($query) {
                    return $query->where('school_id', School::current()->id);
                }),
            ],
            'active' => 'required|boolean',
            'show_on_invoice' => 'required|boolean',
            'invoice_description' => 'nullable',
            'options' => [
                'nullable',
                'array',
                new PaymentMethodDriverOptions($request->input('driver')),
            ],
        ]);

        $school = $request->school();

        $data['tenant_id'] = $school->tenant_id;
        $school->paymentMethods()
            ->create($data);

        session()->flash('success', __('Payment method created successfully.'));

        return redirect()->route('payment-methods.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return $paymentMethod->toResource();
    }

    /**
     * Edit the specified resource.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        $title = __('Update payment method');
        $breadcrumbs = [
            [
                'label' => __('School Settings'),
                'route' => route('settings.school'),
            ],
            [
                'label' => __('Payment Methods'),
                'route' => route('payment-methods.index'),
            ],
            [
                'label' => $paymentMethod->getDriver()->label(),
                'route' => route('payment-methods.edit', $paymentMethod),
            ],
        ];
        $drivers = PaymentMethodDriverResource::collection(
            PaymentMethod::getAllDrivers()
        );

        return inertia('payment-methods/Create', [
            'title' => $title,
            'drivers' => $drivers,
            'breadcrumbs' => $breadcrumbs,
            'paymentMethod' => $paymentMethod->toResource(),
        ])->withViewData(compact('title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'driver' => [
                'required',
                Rule::in(array_keys(PaymentMethod::drivers())),
                Rule::unique('payment_methods')->where(function ($query) {
                    return $query->where('school_id', School::current()->id);
                })->ignoreModel($paymentMethod),
            ],
            'active' => 'required|boolean',
            'show_on_invoice' => 'required|boolean',
            'invoice_description' => 'nullable',
            'options' => [
                'nullable',
                'array',
                new PaymentMethodDriverOptions($request->input('driver')),
            ],
        ]);
        ray($data);

        $paymentMethod->update($data);

        session()->flash('success', __('Payment method updated successfully.'));

        return redirect()->route('payment-methods.index');
    }
}
