<?php

namespace App\Features\Identity\Models;

use App\Features\BusinessFinance\Models\Company;
use App\Features\ProjectAnalysis\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'utilisateur';

    protected $primaryKey = 'id_utilisateur';

    public const CREATED_AT = 'date_creation';
    public const UPDATED_AT = 'date_modification';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe_hash',
    ];

    protected $hidden = [
        'mot_de_passe_hash',
    ];

    protected function casts(): array
    {
        return [
            'mot_de_passe_hash' => 'hashed',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe_hash;
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'id_utilisateur', 'id_utilisateur');
    }
}
