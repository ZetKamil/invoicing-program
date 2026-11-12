<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility
        'company_name',
        'contact_person',
        'email',
        'phone',
        'vat_number',
        'kbo_number',
        'peppol_endpoint_id',
        'address_line1',
        'address', // Backward compatibility alias
        'address_line2',
        'postal_code',
        'zip_code', // Backward compatibility alias
        'city',
        'country_code',
        'country', // Backward compatibility alias
    ];

    /* -------------------------------------------------------------------------
     | Backward Compatibility Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getAddressAttribute(): ?string
    {
        return $this->attributes['address_line1'] ?? null;
    }

    public function setAddressAttribute(?string $value): void
    {
        $this->attributes['address_line1'] = $value ?? 'Onbekend';
    }

    public function getZipCodeAttribute(): ?string
    {
        return $this->attributes['postal_code'] ?? null;
    }

    public function setZipCodeAttribute(?string $value): void
    {
        $this->attributes['postal_code'] = $value ?? '0000';
    }

    public function getCountryAttribute(): ?string
    {
        return $this->attributes['country_code'] ?? 'BE';
    }

    public function setCountryAttribute(?string $value): void
    {
        $this->attributes['country_code'] = $value ? strtoupper(substr($value, 0, 2)) : 'BE';
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function commercialDocuments(): HasMany
    {
        return $this->hasMany(CommercialDocument::class, 'customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'customer_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'converted_customer_id');
    }
}
