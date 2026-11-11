<?php

namespace App\Features\BusinessFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['company_id', 'label'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(FinancialOperation::class, 'operation_tag', 'tag_id', 'operation_id')
            ->withPivot(['company_id', 'associated_at']);
    }
}
