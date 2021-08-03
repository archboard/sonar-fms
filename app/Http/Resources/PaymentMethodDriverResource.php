<?php

namespace App\Http\Resources;

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
        return [
            'key' => $this->resource->key(),
            'label' => $this->resource->label(),
            'description' => $this->resource->description(),
            'component' => $this->resource->component(),
        ];
    }
}
