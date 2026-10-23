<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'sections',
        'status',
        'is_ai_generated',
        'show_global_announcement',
        'views_count',
        'published_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (auth()->check() && ! $post->user_id) {
                $post->user_id = auth()->id();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'                   => PostStatus::class,
            'sections'                 => 'array',
            'is_ai_generated'          => 'boolean',
            'show_global_announcement' => 'boolean',
            'views_count'              => 'integer',
            'published_at'             => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'model_type', 'model_id');
    }

    public function featuredImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable', 'model_type', 'model_id');
    }
}
