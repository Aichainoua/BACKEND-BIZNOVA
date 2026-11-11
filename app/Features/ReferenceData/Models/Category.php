<?php

namespace App\Features\ReferenceData\Models;

use App\Features\BusinessFinance\Models\FinancialOperation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = ['sector_id', 'type', 'label', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(FinancialOperation::class);
    }
}
