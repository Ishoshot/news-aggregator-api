<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArticleAuthorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ArticleAuthor extends Model
{
    /** @use HasFactory<ArticleAuthorFactory> */
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
     * Get the users who have this article author as a preference.
     *
     * This method defines an inverse many-to-many relationship between the ArticleAuthor and User models.
     * It retrieves all users who have selected this article author as one of their preferred authors for news (articles).
     * The relationship uses a pivot table (article_author_user) to link users with their preferred article authors.
     *
     * @codeCoverageIgnore
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->using(ArticleAuthorUser::class);
    }
}
