<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JamesMills\LaravelTimezone\Facades\Timezone;

class InvoiceRefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'refunded_at' => $this->refunded_at,
            'refunded_at_formatted' => $this->refunded_at_formatted,
            'created_at' => Timezone::convertToLocal($this->created_at, 'M j, Y'),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
