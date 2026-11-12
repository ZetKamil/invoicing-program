<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DailyMetric extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'daily_metrics';

    protected $fillable = [
        'tenant_id',
        'date',
        'trackable_type',
        'trackable_id',
        'views_count',
        'whatsapp_clicks',
        'messenger_clicks',
        'phone_clicks',
    ];

    protected function casts(): array
    {
        return [
            'date'             => 'date',
            'views_count'      => 'integer',
            'whatsapp_clicks'  => 'integer',
            'messenger_clicks' => 'integer',
            'phone_clicks'     => 'integer',
        ];
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }
}
