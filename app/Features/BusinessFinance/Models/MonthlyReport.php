<?php

namespace App\Features\BusinessFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReport extends Model
{
    protected $fillable = [
        'company_id',
        'year',
        'month',
        'generated_at',
        'file_path',
        'data_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'generated_at' => 'datetime',
            'data_snapshot' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
