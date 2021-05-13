<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
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
            'fee_id' => $this->fee_id,
            'sync_with_fee' => $this->sync_with_fee,
            'name' => $this->name,
            'amount_per_unit' => $this->amount_per_unit,
            'amount_per_unit_formatted' => $this->amount_per_unit_formatted,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'quantity' => $this->quantity,
        ];
    }
}
