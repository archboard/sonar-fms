<?php

namespace App\Http\Resources;

use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var PaymentMethod $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'driver' => $resource->driver,
            'invoice_description' => $resource->invoice_description,
            'show_on_invoice' => $resource->show_on_invoice,
            'active' => $resource->active,
            'options' => empty($resource->options) ? (object) [] : $resource->options,
            'driver_data' => $resource->includeDriverWithResource
                ? new PaymentMethodDriverResource($resource->getDriver())
                : null,
        ];
    }
}
