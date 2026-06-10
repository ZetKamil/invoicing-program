<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

#[Fillable(['tenant_id', 'company_name', 'contact_person', 'email', 'phone', 'audit_report_url', 'status', 'metadata'])]
class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes, Prunable;

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
            'status' => LeadStatus::class,
            'metadata' => 'array',
        ];
    }

    /**
     * Get the quotes for the lead.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
