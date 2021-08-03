<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'driver' => $this->driver,
            'invoice_description' => $this->invoice_description,
            'show_on_invoice' => $this->show_on_invoice,
            'active' => $this->active,
            'options' => empty($this->options) ? (object) [] : $this->options,
            'driver_data' => new PaymentMethodDriverResource($this->resource->getDriver()),
        ];
    }
}
