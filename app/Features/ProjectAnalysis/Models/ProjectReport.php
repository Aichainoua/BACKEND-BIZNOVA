<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReport extends Model
{
    protected $table = 'rapport_projet';
    protected $primaryKey = 'id_rapport_projet';

    public $timestamps = false;

    protected $fillable = [
        'id_projet',
        'date_generation',
        'periode_debut',
        'periode_fin',
        'devise',
        'nom_fichier',
        'chemin_fichier',
        'instantane_hypotheses',
    ];

    protected function casts(): array
    {
        return [
            'date_generation' => 'datetime',
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'instantane_hypotheses' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_projet', 'id_projet');
    }
}
