<?php

namespace App\Features\ProjectAnalysis\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_projet,
            'sector_id' => $this->id_secteur,
            'name' => $this->nom,
            'launch_date' => $this->date_lancement?->toDateString(),
            'horizon_months' => $this->horizon_mois,
            'country' => $this->pays,
            'currency' => $this->devise,
            'activity_mode' => match ($this->mode_activite) {
                'GLOBAL' => 'global',
                'DETAILLE' => 'detailed',
                default => $this->mode_activite,
            },
            'created_at' => $this->date_creation,
            'updated_at' => $this->date_modification,
        ];
    }
}
