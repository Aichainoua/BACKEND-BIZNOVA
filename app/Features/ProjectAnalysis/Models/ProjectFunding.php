<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFunding extends Model
{
    protected $table = 'financement_projet';
    protected $primaryKey = 'id_financement';

    public $timestamps = false;

    protected $fillable = [
        'id_projet',
        'investissement_initial_total',
        'apport_personnel',
        'financement_hors_emprunt',
        'montant_emprunt',
        'taux_interet_annuel',
        'duree_emprunt_mois',
        'frequence_remboursement',
        'differe_mois',
    ];

    protected function casts(): array
    {
        return [
            'investissement_initial_total' => 'decimal:2',
            'apport_personnel' => 'decimal:2',
            'financement_hors_emprunt' => 'decimal:2',
            'montant_emprunt' => 'decimal:2',
            'taux_interet_annuel' => 'decimal:4',
            'duree_emprunt_mois' => 'integer',
            'differe_mois' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_projet', 'id_projet');
    }
}
