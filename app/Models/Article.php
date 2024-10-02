<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'article_source_id',
        'article_author_id',
        'article_category_id',
        'title',
        'description',
        'content',
        'article_url',
        'cover_image_url',
        'published_at',
    ];

    /**
     * Get the article's source.
     *
     * @return BelongsTo<ArticleSource, Article>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(ArticleSource::class, 'article_source_id');
    }

    /**
     * Get the article's author.
     *
     * @return BelongsTo<ArticleAuthor, Article>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(ArticleAuthor::class, 'article_author_id');
    }

    /**
     * Get the article's category.
     *
     * @return BelongsTo<ArticleCategory, Article>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
