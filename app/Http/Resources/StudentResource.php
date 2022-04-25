<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'sis_id' => $this->sis_id,
            'student_number' => $this->student_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'grade_level' => $this->grade_level,
            'grade_level_formatted' => $this->grade_level_formatted,
            'grade_level_short_formatted' => $this->grade_level_short_formatted,
            'email' => $this->email,
            'enroll_status' => $this->enroll_status,
            'enrolled' => $this->enrolled,
            'current_entry_date' => $this->current_entry_date,
            'current_exit_date' => $this->current_exit_date,
            'initial_district_entry_date' => $this->initial_district_entry_date,
            'initial_school_entry_date' => $this->initial_school_entry_date,
            'account_balance' => $this->account_balance,
            'account_balance_formatted' => $this->account_balance_formatted,
            'revenue' => $this->revenue,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'family' => new FamilyResource($this->whenLoaded('family')),
        ];
    }
}
