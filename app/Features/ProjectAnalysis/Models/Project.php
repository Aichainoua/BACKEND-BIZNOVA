<?php

namespace App\Features\ProjectAnalysis\Models;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $table = 'projet';

    protected $primaryKey = 'id_projet';

    public const CREATED_AT = 'date_creation';
    public const UPDATED_AT = 'date_modification';

    protected $attributes = [
        'horizon_mois' => 12,
        'devise' => 'XAF',
    ];

    protected $fillable = [
        'id_utilisateur',
        'id_secteur',
        'nom',
        'date_lancement',
        'horizon_mois',
        'pays',
        'devise',
        'mode_activite',
    ];

    protected function casts(): array
    {
        return [
            'date_lancement' => 'date',
            'horizon_mois' => 'integer',
        ];
    }

    
public function user(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'id_utilisateur',
        'id_utilisateur'
    );
}


    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'id_secteur', 'id_secteur');
    }

    public function funding(): HasOne
    {
        return $this->hasOne(ProjectFunding::class, 'id_projet', 'id_projet');
    }

    public function activityLines(): HasMany
    {
        return $this->hasMany(ProjectActivityLine::class, 'id_projet', 'id_projet');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class, 'id_projet', 'id_projet');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProjectReport::class, 'id_projet', 'id_projet');
    }


public function getRouteKeyName(): string
{
    return 'id_projet';
}

}
