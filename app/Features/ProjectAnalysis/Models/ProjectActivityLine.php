<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivityLine extends Model
{
    protected $fillable = [
        'project_id',
        'line_type',
        'designation',
        'monthly_revenue',
        'monthly_cost_of_goods_sold',
        'unit_price',
        'monthly_volume',
        'unit_direct_cost',
        'monthly_subscription_price',
        'new_subscribers_per_month',
        'active_subscribers',
    ];

    protected function casts(): array
    {
        return [
            'monthly_revenue' => 'decimal:2',
            'monthly_cost_of_goods_sold' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'monthly_volume' => 'decimal:2',
            'unit_direct_cost' => 'decimal:2',
            'monthly_subscription_price' => 'decimal:2',
            'new_subscribers_per_month' => 'integer',
            'active_subscribers' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
