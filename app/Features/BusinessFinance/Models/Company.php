<?php

namespace App\Features\BusinessFinance\Models;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $attributes = [
        'currency' => 'XAF',
    ];

    protected $fillable = [
        'user_id',
        'sector_id',
        'name',
        'currency',
        'opening_balance',
        'opening_balance_date',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(FinancialOperation::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(MonthlyBudget::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MonthlyReport::class);
    }
}
