<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFunding extends Model
{
    protected $fillable = [
        'project_id',
        'personal_contribution',
        'non_loan_funding',
        'loan_amount',
        'annual_interest_rate',
        'loan_duration_months',
        'repayment_frequency',
        'deferment_months',
    ];

    protected function casts(): array
    {
        return [
            'personal_contribution' => 'decimal:2',
            'non_loan_funding' => 'decimal:2',
            'loan_amount' => 'decimal:2',
            'annual_interest_rate' => 'decimal:4',
            'loan_duration_months' => 'integer',
            'deferment_months' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
