<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpense extends Model
{
    protected $fillable = [
        'project_id',
        'nature',
        'label',
        'monthly_amount',
        'start_month',
        'end_month',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'decimal:2',
            'start_month' => 'integer',
            'end_month' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
