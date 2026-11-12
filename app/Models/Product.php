<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'name',
        'description',
        'price',
        'type',
        'is_recurring',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'type'         => ProductType::class,
            'is_recurring' => 'boolean',
        ];
    }
}
