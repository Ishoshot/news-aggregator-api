<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     *  Get the article sources associated with the user.
     *
     * This method defines a many-to-many relationship between the User and ArticleSource models.
     * It retrieves all the article sources that the user has preferred, allowing for the
     * management of user preferences regarding news (articles) sources. The relationship uses a pivot table
     * (article_source_user) to link users and their preferred article sources,
     * and it automatically manages the timestamps for when the relationship is created or updated.
     *
     * @return BelongsToMany<ArticleSource>
     */
    public function articleSources(): BelongsToMany
    {
        return $this->belongsToMany(ArticleSource::class)->withTimestamps()->using(ArticleSourceUser::class);
    }

    /**
     *  Get the article categories associated with the user.
     *
     * This method defines a many-to-many relationship between the User and ArticleCategory models.
     * It retrieves all the article categories that the user has preferred, allowing for the
     * management of user preferences regarding news (articles) categories. The relationship uses a pivot table
     * (article_category_user) to link users and their preferred article categories,
     * and it automatically manages the timestamps for when the relationship is created or updated.
     *
     * @return BelongsToMany<ArticleCategory>
     */
    public function articleCategories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class)->withTimestamps()->using(ArticleCategoryUser::class);
    }

    /**
     *  Get the article authors associated with the user.
     *
     * This method defines a many-to-many relationship between the User and ArticleAuthor models.
     * It retrieves all the article authors that the user has preferred, allowing for the
     * management of user preferences regarding news (articles) authors. The relationship uses a pivot table
     * (article_author_user) to link users and their preferred article authors,
     * and it automatically manages the timestamps for when the relationship is created or updated.
     *
     * @return BelongsToMany<ArticleAuthor>
     */
    public function articleAuthors(): BelongsToMany
    {
        return $this->belongsToMany(ArticleAuthor::class)->withTimestamps()->using(ArticleAuthorUser::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
