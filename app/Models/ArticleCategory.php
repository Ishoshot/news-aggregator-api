<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ArticleCategory extends Model
{
    /** @use HasFactory<ArticleCategoryFactory> */
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
     * Get the users who have this article category as a preference.
     *
     * This method defines an inverse many-to-many relationship between the ArticleCategory and User models.
     * It retrieves all users who have selected this article category as one of their preferred categories for news (articles).
     * The relationship uses a pivot table (article_category_user) to link users with their preferred article categories.
     *
     * @codeCoverageIgnore
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->using(ArticleCategoryUser::class);
    }
}
