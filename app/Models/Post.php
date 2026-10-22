<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bedrijf_id', 'user_id', 'category_id',
    'title', 'slug', 'excerpt', 'body', 'sections',
    'status', 'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes, Prunable;

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (auth()->check() && ! $post->user_id) {
                $post->user_id = auth()->id();
            }
        });
    }

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
            'status'       => PostStatus::class,
            'sections'     => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Category associated with this blog post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Author of the blog post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All media items associated with the post.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Featured image for the blog post.
     */
    public function featuredImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('is_featured', true);
    }
}
