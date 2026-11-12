<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSchedule extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'ai_schedules';

    protected $fillable = [
        'tenant_id',
        'is_enabled',
        'frequency',
        'day_of_week',
        'preferred_hour',
        'auto_publish',
        'target_category_id',
        'niche_guidelines',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'     => 'boolean',
            'auto_publish'   => 'boolean',
            'day_of_week'    => 'integer',
            'preferred_hour' => 'integer',
            'last_run_at'    => 'datetime',
            'next_run_at'    => 'datetime',
        ];
    }

    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'target_category_id');
    }
}
