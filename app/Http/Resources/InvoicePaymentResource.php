<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JamesMills\LaravelTimezone\Facades\Timezone;

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
            'remaining_balance' => $this->remaining_balance,
            'remaining_balance_formatted' => $this->remaining_balance_formatted,
            'paid_at' => $this->paid_at,
            'paid_at_formatted' => $this->paid_at_formatted,
            'parent_uuid' => $this->parent_uuid,
            'transaction_details' => $this->transaction_details,
            'edited' => $this->edited,
            'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
            'made_by' => new UserResource($this->whenLoaded('madeBy')),
            'payment_term' => new InvoicePaymentTermResource($this->whenLoaded('invoicePaymentTerm')),
            'schedule' => new InvoicePaymentScheduleResource($this->whenLoaded('invoicePaymentSchedule')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'created_at' => Timezone::convertToLocal($this->created_at, 'M j, Y'),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
        ];
    }
}
