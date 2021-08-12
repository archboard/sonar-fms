<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTaxItemResource extends JsonResource
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
            'uuid' => $this->uuid,
            'invoice_item_uuid' => $this->invoice_item_uuid,
            'invoice_item' => new InvoiceItemResource($this->whenLoaded('invoiceItem')),
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
        ];
    }
}
