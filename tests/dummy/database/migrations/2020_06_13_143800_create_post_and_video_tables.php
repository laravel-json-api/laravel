<?php
/*
 * Copyright 2021 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostAndVideoTables extends Migration
{

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
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

        Schema::create('images', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->timestamps();
            $table->string('url');
        });

        Schema::create('videos', function (Blueprint $table) {
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

        Schema::create('comments', function (Blueprint $table) {
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

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        Schema::create('taggables', function (Blueprint $table) {
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

        Schema::create('image_post', function (Blueprint $table) {
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

        Schema::create('post_video', function (Blueprint $table) {
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
}
