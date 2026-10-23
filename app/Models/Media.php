<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'media';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'model_type',
        'model_id',
        'collection_name',
        'file_name',
        'mime_type',
        'disk',
        'size_bytes',
        'custom_properties',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes'        => 'integer',
            'custom_properties' => 'array',
            'sort_order'        => 'integer',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /* -------------------------------------------------------------------------
     | Backward Compatibility Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getMediableTypeAttribute(): ?string
    {
        return $this->attributes['model_type'] ?? null;
    }

    public function setMediableTypeAttribute(?string $value): void
    {
        $this->attributes['model_type'] = $value;
    }

    public function getMediableIdAttribute(): ?string
    {
        return $this->attributes['model_id'] ?? null;
    }

    public function setMediableIdAttribute(?string $value): void
    {
        $this->attributes['model_id'] = $value;
    }

    public function getFileSizeAttribute(): ?int
    {
        return $this->attributes['size_bytes'] ?? null;
    }

    public function setFileSizeAttribute(?int $value): void
    {
        $this->attributes['size_bytes'] = $value;
    }
}
