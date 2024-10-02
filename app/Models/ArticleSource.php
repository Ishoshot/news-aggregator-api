<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArticleSourceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ArticleSource extends Model
{
    /** @use HasFactory<ArticleSourceFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the users who have this article source as a preference.
     *
     * This method defines an inverse many-to-many relationship between the ArticleSource and User models.
     * It retrieves all users who have selected this article source as one of their preferred sources for news.
     * The relationship uses a pivot table (article_source_user) to link users with their preferred article sources.
     *
     * @codeCoverageIgnore
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->using(ArticleSourceUser::class);
    }
}
