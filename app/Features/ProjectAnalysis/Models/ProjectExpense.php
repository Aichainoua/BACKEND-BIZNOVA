<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpense extends Model
{
    protected $table = 'poste_charge_projet';
    protected $primaryKey = 'id_poste_charge';

    public $timestamps = false;

    protected $fillable = [
        'id_projet',
        'nature_charge',
        'libelle',
        'montant_mensuel',
        'date_debut_mois',
        'date_fin_mois',
    ];

    protected function casts(): array
    {
        return [
            'montant_mensuel' => 'decimal:2',
            'date_debut_mois' => 'integer',
            'date_fin_mois' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_projet', 'id_projet');
    }
}
