<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxRate extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'tax_rates';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'rate_percentage',
        'peppol_category_code',
        'peppol_exemption_code',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_percentage' => 'decimal:2',
            'is_default'      => 'boolean',
            'is_active'       => 'boolean',
        ];
    }

    public function documentLines(): HasMany
    {
        return $this->hasMany(CommercialDocumentLine::class, 'tax_rate_id');
    }
}
