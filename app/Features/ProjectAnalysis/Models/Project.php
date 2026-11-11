<?php

namespace App\Features\ProjectAnalysis\Models;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $attributes = [
        'horizon_months' => 12,
        'currency' => 'XAF',
    ];

    protected $fillable = [
        'user_id',
        'sector_id',
        'name',
        'launch_date',
        'horizon_months',
        'country',
        'currency',
        'activity_mode',
    ];

    protected function casts(): array
    {
        return [
            'launch_date' => 'date',
            'horizon_months' => 'integer',
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

    public function funding(): HasOne
    {
        return $this->hasOne(ProjectFunding::class);
    }

    public function activityLines(): HasMany
    {
        return $this->hasMany(ProjectActivityLine::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProjectReport::class);
    }
}
