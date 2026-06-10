<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id', 'customer_type', 'customer_id', 'invoice_number',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at'
])]
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['customer'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'last_reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Get the customer (Lead or User) that owns the invoice.
     */
    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the items associated with the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Recalculate invoice totals based on items.
     */
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * unit_price'));
        $this->tax_total = $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * unit_price * (tax_rate / 100)'));
        $this->total_amount = $this->subtotal + $this->tax_total;
        $this->saveQuietly();
    }
}
