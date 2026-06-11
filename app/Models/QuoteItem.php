<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quote_id', 'description', 'quantity', 'unit_price', 'tax_rate', 'total'])]
class QuoteItem extends Model
{
    use HasFactory, HasUlids;

    /**
     * The relations to eager load on every query.
     * Required by QuoteItemPolicy which checks $quoteItem->quote->bedrijf_id
     * to avoid N+1 database queries during Filament list views.
     *
     * @var array<string>
     */
    protected $with = ['quote'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
