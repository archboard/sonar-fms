<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentMethodDriverResource;
use App\Models\School;
use Illuminate\Http\Request;

class FetchPaymentMethodsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param School $school
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(School $school)
    {
        $paymentMethods = $school->getPaymentMethods();

        return PaymentMethodDriverResource::collection($paymentMethods);
    }
}
