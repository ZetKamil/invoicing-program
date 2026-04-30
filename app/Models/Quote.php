<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'lead_id', 'quote_number', 'total_amount', 'valid_until', 'status', 'last_reminded_at'])]
class Quote extends Model
{
    /** @use HasFactory<\Database\Factories\QuoteFactory> */
    use HasFactory, HasTenant, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'total_amount' => 'decimal:2',
            'status' => QuoteStatus::class,
            'last_reminded_at' => 'datetime',
        ];
    }

    /**
     * Get the lead that owns the quote.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Scope a query to only include overdue/expired quotes.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', '!=', QuoteStatus::ACCEPTED)
              ->where('status', '!=', QuoteStatus::DECLINED)
              ->where('valid_until', '<', now()->startOfDay());
    }
}
