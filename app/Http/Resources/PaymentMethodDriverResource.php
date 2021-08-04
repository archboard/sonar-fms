<?php

namespace App\Http\Resources;

use App\PaymentMethods\PaymentMethodDriver;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodDriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var PaymentMethodDriver $resource */
        $resource = $this->resource;

        return [
            'key' => $resource->key(),
            'label' => $resource->label(),
            'description' => $resource->description(),
            'component' => $resource->component(),
            'payment_method' => $resource->includePaymentMethodInResource()
                ? new PaymentMethodResource($resource->getPaymentMethod())
                : null,
        ];
    }
}
