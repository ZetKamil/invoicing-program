<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationLog extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'ai_generation_logs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'post_id',
        'model_used',
        'topic_prompt',
        'input_tokens',
        'output_tokens',
        'estimated_cost_micro_usd',
        'execution_time_ms',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens'             => 'integer',
            'output_tokens'            => 'integer',
            'estimated_cost_micro_usd' => 'integer',
            'execution_time_ms'        => 'integer',
            'created_at'               => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
