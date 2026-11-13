<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivityLine extends Model
{
    protected $table = 'ligne_activite_projet';
    protected $primaryKey = 'id_ligne_activite';

    public $timestamps = false;

    protected $fillable = [
        'id_projet',
        'type_ligne',
        'designation',
        'ca_mensuel_saisi',
        'cogs_mensuel_saisi',
        'prix_unitaire',
        'volume_mensuel',
        'cout_direct_unitaire',
        'prix_abonnement_mensuel',
        'nouveaux_abonnes_mois',
        'abonnes_actifs',
    ];

    protected function casts(): array
    {
        return [
            'ca_mensuel_saisi' => 'decimal:2',
            'cogs_mensuel_saisi' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'volume_mensuel' => 'decimal:2',
            'cout_direct_unitaire' => 'decimal:2',
            'prix_abonnement_mensuel' => 'decimal:2',
            'nouveaux_abonnes_mois' => 'integer',
            'abonnes_actifs' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_projet', 'id_projet');
    }
}
