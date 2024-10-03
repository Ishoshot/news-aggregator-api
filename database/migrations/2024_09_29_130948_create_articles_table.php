<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('article_source_id')->index()->nullable()->constrained('article_sources')->onDelete('cascade');
            $table->foreignUuid('article_author_id')->index()->nullable()->constrained('article_authors')->onDelete('cascade');
            $table->foreignUuid('article_category_id')->index()->nullable()->constrained('article_categories')->onDelete('cascade');

            $table->string('title')->index()->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('article_url')->unique();
            $table->string('cover_image_url')->nullable();
            $table->timestamp('published_at')->index()->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
