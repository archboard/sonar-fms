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
            'invoice_number' => $this->invoice_number,
            'uuid' => $this->uuid,
            'batch_id' => $this->batch_id,
            'title' => $this->title,
            'term_id' => $this->term_id,
            'notify' => $this->notify,
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
            'published_at' => $this->published_at,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'voided_at' => $this->voided_at,
            'is_void' => $this->is_void,
            'notified_at' => $this->notified_at,
            'status_color' => $this->status_color,
            'status_label' => $this->status_label,
            'past_due' => $this->past_due,
            'payment_made' => $this->payment_made,
            'student_uuid' => $this->student_uuid,
            'is_parent' => $this->is_parent,
            'parent_uuid' => $this->parent_uuid,
            'parent' => new InvoiceResource($this->whenLoaded('parent')),
            'apply_tax' => $this->apply_tax,
            'use_school_tax_defaults' => $this->use_school_tax_defaults,
            'tax_due' => $this->tax_due,
            'tax_due_formatted' => $this->tax_due_formatted,
            'tax_rate' => $this->tax_rate,
            'tax_label' => $this->tax_label,
            'tax_rate_formatted' => $this->tax_rate_formatted,
            'tax_rate_converted' => $this->tax_rate_converted,
            'children_count' => $this->children_count,
            'student_list' => $this->student_list,
            'student' => new StudentResource($this->whenLoaded('student')),
            'students' => $this->whenLoaded('students', StudentResource::collection($this->students), []),
            'school' => new SchoolResource($this->whenLoaded('school')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),
            'scholarships' => InvoiceScholarshipResource::collection($this->whenLoaded('invoiceScholarships')),
            'payment_schedules' => InvoicePaymentScheduleResource::collection($this->whenLoaded('invoicePaymentSchedules')),
            'payments' => InvoicePaymentResource::collection($this->whenLoaded('invoicePayments')),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'children' => InvoiceResource::collection($this->whenLoaded('children')),
        ];
    }
}
