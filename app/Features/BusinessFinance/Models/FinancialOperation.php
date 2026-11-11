<?php

namespace App\Features\BusinessFinance\Models;

use App\Features\ReferenceData\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinancialOperation extends Model
{
    protected $fillable = [
        'company_id',
        'category_id',
        'amount',
        'operation_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'operation_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'operation_tag', 'operation_id', 'tag_id')
            ->withPivot(['company_id', 'associated_at']);
    }
}
