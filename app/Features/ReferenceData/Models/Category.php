<?php

namespace App\Features\ReferenceData\Models;

use App\Features\BusinessFinance\Models\FinancialOperation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categorie';

    protected $primaryKey = 'id_categorie';

    public $timestamps = false;

    protected $fillable = [
        'id_secteur',
        'type_operation',
        'libelle',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'id_secteur', 'id_secteur');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(FinancialOperation::class, 'id_categorie', 'id_categorie');
    }
}
