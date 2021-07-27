<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentTermResource extends JsonResource
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
            'percentage' => $this->percentage,
            'amount_due' => $this->amount_due,
            'remaining_balance' => $this->remaining_balance,
            'due_at' => $this->due_at,
            'notified_at' => $this->notified_at,
            'notify' => $this->notify,
        ];
    }
}
