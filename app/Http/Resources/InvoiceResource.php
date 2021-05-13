<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'amount_due' => $this->amount_due,
            'amount_due_formatted' => $this->amount_due_formatted,
            'remaining_balance' => $this->remaining_balance,
            'remaining_balance_formatted' => $this->remaining_balance_formatted,
            'available_at' => $this->available_at,
            'created_at' => $this->created_at,
            'available' => $this->available,
            'due_at' => $this->due_at,
            'paid_at' => $this->paid_at,
            'notified_at' => $this->notified_at,
            'status_color' => $this->status_color,
            'status_label' => $this->status_label,
            'past_due' => $this->past_due,
            'payment_made' => $this->payment_made,
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),
            'scholarships' => InvoiceScholarshipResource::collection($this->whenLoaded('invoiceScholarships')),
        ];
    }
}
