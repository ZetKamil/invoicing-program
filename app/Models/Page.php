<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'pages';

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'is_homepage',
        'is_published',
        'blocks',
        'seo_meta',
    ];

    protected function casts(): array
    {
        return [
            'is_homepage'  => 'boolean',
            'is_published' => 'boolean',
            'blocks'       => 'array',
            'seo_meta'     => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('is_homepage', true);
    }
}
