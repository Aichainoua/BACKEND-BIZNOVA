<?php

namespace App\Features\BusinessFinance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OperationTag extends Pivot
{
    protected $table = 'operation_tag';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['company_id', 'operation_id', 'tag_id', 'associated_at'];

    protected function casts(): array
    {
        return [
            'associated_at' => 'datetime',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
