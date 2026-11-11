<?php

namespace App\Features\ProjectAnalysis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReport extends Model
{
    protected $fillable = [
        'project_id',
        'generated_at',
        'file_name',
        'file_path',
        'data_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'data_snapshot' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
