<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReport extends Model
{
    protected $fillable = [
        'project_id',
        'generated_at',
        'period_start',
        'period_end',
        'currency',
        'file_name',
        'file_path',
        'data_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'period_start' => 'date',
            'period_end' => 'date',
            'data_snapshot' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
