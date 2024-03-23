<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('author_id');
            $table->timestamp('published_at')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('synopsis');
            $table->text('content');

            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::create('images', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->timestamps();
            $table->string('url');
        });

        Schema::create('videos', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->timestamps();
            $table->unsignedBigInteger('owner_id');
            $table->string('title');
            $table->string('url');

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');

            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        Schema::create('taggables', function (Blueprint $table): void {
            $table->unsignedBigInteger('tag_id');
            $table->morphs('taggable');
            $table->primary([
                'tag_id',
                'taggable_type',
                'taggable_id',
            ]);

            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('image_post', function (Blueprint $table): void {
            $table->uuid('image_uuid');
            $table->unsignedBigInteger('post_id');
            $table->primary(['image_uuid', 'post_id']);

            $table->foreign('image_uuid')
                ->references('uuid')
                ->on('images')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('post_video', function (Blueprint $table): void {
            $table->unsignedBigInteger('post_id');
            $table->uuid('video_uuid');
            $table->primary(['post_id', 'video_uuid']);

            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('video_uuid')
                ->references('uuid')
                ->on('videos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('posts');
    }
};
