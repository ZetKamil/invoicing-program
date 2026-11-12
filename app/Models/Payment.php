<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'amount_minor',
        'currency',
        'method',
        'status',
        'transaction_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'paid_at'      => 'datetime',
        ];
    }

    public function getAmountAttribute(): float
    {
        return (float) ($this->amount_minor / 100);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'document_id');
    }
}
