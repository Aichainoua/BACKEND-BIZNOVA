<?php

namespace App\Features\ProjectAnalysis\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sector_id' => $this->sector_id,
            'name' => $this->name,
            'launch_date' => $this->launch_date?->toDateString(),
            'horizon_months' => $this->horizon_months,
            'country' => $this->country,
            'currency' => $this->currency,
            'activity_mode' => $this->activity_mode,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
