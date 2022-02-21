<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
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
            'receipt_number' => $this->receipt_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->format('F j, Y'),
            'payment' => new InvoicePaymentResource($this->whenLoaded('invoicePayment')),
        ];
    }
}
