<?php

namespace App\Http\Resources;

use App\Models\PaymentMethod;
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
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = $resource->getPaymentMethod();

        $list = $paymentMethod?->id
            ? [$paymentMethod?->id, ...$resource->getImportDetectionValues()]
            : $resource->getImportDetectionValues();

        return [
            'key' => $resource->key(),
            'label' => $resource->label(),
            'description' => $resource->description(),
            'component' => $resource->component(),
            'detects_list' => implode(', ', $list),
            'payment_method' => $resource->includePaymentMethodInResource()
                ? new PaymentMethodResource($paymentMethod)
                : null,
        ];
    }
}
