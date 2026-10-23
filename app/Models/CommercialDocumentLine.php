<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialDocumentLine extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'commercial_document_lines';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'catalog_item_id',
        'tax_rate_id',
        'description',
        'quantity',
        'unit_price_minor',
        'discount_percentage',
        'line_net_minor',
        'line_tax_minor',
        'line_gross_minor',
        'snapshot_tax_rate',
        'snapshot_peppol_cat',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity'            => 'decimal:3',
            'unit_price_minor'    => 'integer',
            'discount_percentage' => 'decimal:2',
            'line_net_minor'      => 'integer',
            'line_tax_minor'      => 'integer',
            'line_gross_minor'    => 'integer',
            'snapshot_tax_rate'   => 'decimal:2',
            'sort_order'          => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'document_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_item_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }
}
