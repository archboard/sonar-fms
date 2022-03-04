<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'student_selection' => $this->student_selection,
            'invoice_selection' => $this->invoice_selection,
            'timezone' => $this->timezone,
            'time_format' => $this->time_format,
            'school_id' => $this->school_id,
            'locale' => $this->locale,
            'manages_tenancy' => $this->manages_tenancy,
            'schools' => SchoolResource::collection($this->whenLoaded('activeSchools')),
            'school' => new SchoolResource($this->whenLoaded('school')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name'), []),
        ];
    }
}
