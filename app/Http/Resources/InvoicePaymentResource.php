<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'paid_at' => $this->paid_at,
            'paid_at_formatted' => $this->paid_at_formatted,
            'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
            'made_by' => new UserResource($this->whenLoaded('madeBy')),
            'paymentTerm' => new InvoicePaymentTermResource($this->whenLoaded('invoicePaymentTerm')),
            'schedule' => new InvoicePaymentTermResource($this->whenLoaded('invoicePaymentSchedule')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }
}
