<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentScheduleResource extends JsonResource
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
            'id' => $this->uuid,
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'terms' => InvoicePaymentTermResource::collection($this->whenLoaded('invoicePaymentTerms')),
        ];
    }
}
