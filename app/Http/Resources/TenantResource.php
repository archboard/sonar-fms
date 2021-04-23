<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
            'domain' => $this->domain,
            'license' => $this->license,
            'subscription_started_at' => $this->subscription_started_at,
            'allow_oidc_login' => $this->allow_oidc_login,
            'allow_password_auth' => $this->allow_password_auth,
            'sync_times' => SyncTimeResource::collection($this->whenLoaded('syncTimes')),
            'schools' => SchoolResource::collection($this->whenLoaded('schools')),
            'is_syncing' => !!$this->batch_id,
            'is_cloud' => config('app.cloud'),
        ];
    }
}
