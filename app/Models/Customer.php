<?php

namespace App\Models;

use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

#[Fillable(['bedrijf_id', 'company_name', 'vat_number', 'contact_person', 'email', 'phone', 'address', 'zip_code', 'city', 'country', 'metadata'])]
class Customer extends Model
{
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes, Prunable;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subYear());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the invoices for the customer.
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'customer');
    }
}
