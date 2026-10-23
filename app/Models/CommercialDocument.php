<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialDocument extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'commercial_documents';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'parent_document_id',
        'type',
        'document_number',
        'status',
        'billing_period',
        'currency',
        'exchange_rate',
        'subtotal_minor',
        'tax_total_minor',
        'total_minor',
        'issued_at',
        'due_at',
        'paid_at',
        'vcs_reference',
        'ubl_xml_path',
        'peppol_message_id',
        'snapshot_customer_name',
        'snapshot_customer_vat',
        'snapshot_customer_address',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate'             => 'decimal:6',
            'subtotal_minor'            => 'integer',
            'tax_total_minor'           => 'integer',
            'total_minor'               => 'integer',
            'issued_at'                 => 'date',
            'due_at'                    => 'date',
            'paid_at'                   => 'datetime',
            'snapshot_customer_address' => 'array',
        ];
    }

    /* -------------------------------------------------------------------------
     | Minor Units Conversion Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getTotalAttribute(): float
    {
        return (float) ($this->total_minor / 100);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->subtotal_minor / 100);
    }

    public function getTaxTotalAttribute(): float
    {
        return (float) ($this->tax_total_minor / 100);
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'parent_document_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CommercialDocumentLine::class, 'document_id')->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'document_id');
    }

    /* -------------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------------- */

    public function scopeInvoices(Builder $query): Builder
    {
        return $query->where('type', 'invoice');
    }

    public function scopeQuotes(Builder $query): Builder
    {
        return $query->where('type', 'quote');
    }
}
