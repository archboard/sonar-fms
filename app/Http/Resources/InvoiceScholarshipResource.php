<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceScholarshipResource extends JsonResource
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
            'scholarship_id' => $this->scholarship_id,
            'name' => $this->name,
            'sync_with_scholarship' => $this->sync_with_scholarship,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'calculated_amount' => $this->calculated_amount,
            'calculated_amount_formatted' => $this->calculated_amount_formatted,
            'percentage' => $this->percentage,
            'percentage_converted' => $this->percentage_converted,
            'percentage_formatted' => $this->percentage_formatted,
            'resolution_strategy' => $this->resolution_strategy,
            'applies_to' => $this->whenLoaded(
                'appliesTo',
                fn () => $this->appliesTo->pluck('uuid'),
                []
            ),
        ];
    }
}
