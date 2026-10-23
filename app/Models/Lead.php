<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'leads';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility
        'name',
        'contact_person', // Backward compatibility alias
        'email',
        'phone',
        'company_name',
        'source',
        'status',
        'audit_report_url',
        'notes',
        'converted_customer_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
        ];
    }

    /* -------------------------------------------------------------------------
     | Backward Compatibility Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getContactPersonAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function setContactPersonAttribute(?string $value): void
    {
        $this->attributes['name'] = $value ?? 'Contactpersoon';
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'customer_id');
    }
}
