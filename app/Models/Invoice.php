<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bedrijf_id', 'customer_type', 'customer_id', 'quote_id', 'invoice_number', 'trailer_type',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'notes', 'cmr_number', 'truck_license_plate', 'trailer_license_plate',
    'loading_address', 'delivery_address', 'loading_date', 'delivery_date', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at', 'cargo_weight_kg', 'pallet_count', 'incoterms', 'driver_name', 'driver_phone', 'is_reverse_charge', 'cmr_document_path'
])]
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes;

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
            'loading_date' => 'date',
            'delivery_date' => 'date',
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
     * Get the quote that generated this invoice.
     */
    public function quote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the items associated with the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotals(): void
    {
        $sub = '0.00';
        $tax = '0.00';
        $total = '0.00';

        foreach ($this->items as $item) {
            $qty = (string) $item->quantity;
            $price = (string) $item->unit_price;
            $taxRate = (string) $item->tax_rate;

            $lineSubtotal = bcmul($qty, $price, 10);
            $taxMultiplier = bcdiv($taxRate, '100', 10);
            $lineTax = bcmul($lineSubtotal, $taxMultiplier, 10);

            $sub = bcadd($sub, $lineSubtotal, 10);
            $tax = bcadd($tax, $lineTax, 10);
        }

        $this->subtotal = bcadd($sub, '0', 2);
        $this->tax_total = bcadd($tax, '0', 2);
        
        // Zgodnie z zasadami ksiÄ™gowoĹ›ci: kwota caĹ‚kowita = suma netto + suma podatku.
        // Gwarantuje to brak rozjazdu o 1 grosz na Ĺ‚Ä…cznym dokumencie.
        $this->total_amount = bcadd($this->subtotal, $this->tax_total, 2);
        
        $this->saveQuietly();
    }
}
