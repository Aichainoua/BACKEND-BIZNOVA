<?php

namespace App\Features\ReferenceData\Models;

use App\Features\BusinessFinance\Models\Company;
use App\Features\ProjectAnalysis\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'label'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
