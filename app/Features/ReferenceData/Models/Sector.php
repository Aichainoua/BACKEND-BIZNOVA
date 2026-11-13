<?php

namespace App\Features\ReferenceData\Models;

use App\Features\BusinessFinance\Models\Company;
use App\Features\ProjectAnalysis\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    protected $table = 'secteur';

    protected $primaryKey = 'id_secteur';

    public $timestamps = false;

    protected $fillable = ['code', 'libelle'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'id_secteur', 'id_secteur');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'id_secteur', 'id_secteur');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'id_secteur', 'id_secteur');
    }
}
