<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create article_categories table
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#4e73df');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default categories
        DB::table('article_categories')->insert([
            ['name' => 'Faith', 'slug' => 'faith', 'color' => '#4e73df', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Church', 'slug' => 'church', 'color' => '#1cc88a', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ministry', 'slug' => 'ministry', 'color' => '#36b9cc', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Family', 'slug' => 'family', 'color' => '#f6c23e', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Culture', 'slug' => 'culture', 'color' => '#e74a3b', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'News', 'slug' => 'news', 'color' => '#858796', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create article_tags pivot table
        Schema::create('article_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('article_id');
            $table->string('tag');
            $table->unique(['article_id', 'tag']);
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });

        // Add new columns to articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->unsignedBigInteger('category_id')->nullable()->after('slug');
            $table->text('excerpt')->nullable()->after('description');
            $table->string('image_caption')->nullable()->after('banner');
            $table->string('scripture_reference')->nullable()->after('image_caption');
            $table->boolean('is_featured')->default(false)->after('scripture_reference');
            $table->integer('reading_time')->default(1)->after('is_featured');
            $table->integer('views')->default(0)->after('reading_time');

            $table->foreign('category_id')->references('id')->on('article_categories')->onDelete('set null');
        });

        // Backfill existing articles with slugs and reading_time
        $articles = DB::table('articles')->get();
        foreach ($articles as $article) {
            $slug = \Str::slug($article->title);
            // Ensure uniqueness
            $existing = DB::table('articles')->where('slug', $slug)->where('id', '!=', $article->id)->count();
            if ($existing > 0) {
                $slug = $slug . '-' . $article->id;
            }
            $wordCount = str_word_count(strip_tags($article->description ?? ''));
            $readingTime = max(1, (int) ceil($wordCount / 200));

            DB::table('articles')->where('id', $article->id)->update([
                'slug' => $slug,
                'reading_time' => $readingTime,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['slug', 'category_id', 'excerpt', 'image_caption', 'scripture_reference', 'is_featured', 'reading_time', 'views']);
        });

        Schema::dropIfExists('article_tags');
        Schema::dropIfExists('article_categories');
    }
};
