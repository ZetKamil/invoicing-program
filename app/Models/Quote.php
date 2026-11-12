<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    /** @use HasFactory<\Database\Factories\QuoteFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;

    protected $table = 'quotes';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'customer_id',
        'quote_number',
        'trailer_type',
        'loading_address',
        'delivery_address',
        'description',
        'metadata',
        'total_amount',
        'valid_until',
        'status',
        'last_reminded_at',
        'cargo_weight_kg',
        'pallet_count',
        'loading_date',
        'delivery_date',
        'incoterms',
        'cmr_number',
        'truck_license_plate',
        'trailer_license_plate',
        'driver_name',
        'driver_phone',
        'is_reverse_charge',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_until'      => 'date',
            'loading_date'     => 'date',
            'delivery_date'    => 'date',
            'total_amount'     => 'decimal:2',
            'status'           => QuoteStatus::class,
            'last_reminded_at' => 'datetime',
            'metadata'         => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', '!=', QuoteStatus::ACCEPTED)
              ->where('status', '!=', QuoteStatus::DECLINED)
              ->where('valid_until', '<', now()->startOfDay());
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculateTotals(): void
    {
        $total = '0.00';
        foreach ($this->items as $item) {
            $total = bcadd($total, (string) $item->total, 2);
        }
        $this->total_amount = $total;
        $this->saveQuietly();
    }
}
