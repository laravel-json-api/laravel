<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

declare(strict_types=1);

namespace App\Tests\Api\V1\Posts;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use App\Tests\Api\V1\TestCase;

class AttachMediaTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $existingImages = Image::factory()->count(2)->create();
        $existingVideos = Video::factory()->count(2)->create();

        $this->post->images()->saveMany($existingImages);
        $this->post->videos()->saveMany($existingVideos);

        $images = Image::factory()->count(2)->create();
        $videos = Video::factory()->count(2)->create();

        $ids = collect($images)->merge($videos)->map(fn($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('videos') // @TODO assertions should work without this.
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertNoContent();

        $this->assertDatabaseCount('image_post', $images->count() + $existingImages->count());
        $this->assertDatabaseCount('post_video', $videos->count() + $existingVideos->count());

        foreach ($existingImages->merge($images) as $image) {
            $this->assertDatabaseHas('image_post', [
                'image_uuid' => $image->getKey(),
                'post_id' => $this->post->getKey(),
            ]);
        }

        foreach ($existingVideos->merge($videos) as $video) {
            $this->assertDatabaseHas('post_video', [
                'post_id' => $this->post->getKey(),
                'video_uuid' => $video->getKey(),
            ]);
        }
    }

    public function testInvalid(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            [
                'type' => 'comments',
                'id' => (string) $comment->getRouteKey(),
            ],
        ];

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('videos')
            ->withData($data)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertExactErrorStatus([
            'detail' => 'The media field must be a to-many relationship containing images, videos resources.',
            'source' => ['pointer' => '/data/0'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    public function testUnauthorized(): void
    {
        $existingImages = Image::factory()->count(1)->create();
        $existingVideos = Video::factory()->count(1)->create();

        $this->post->images()->saveMany($existingImages);
        $this->post->videos()->saveMany($existingVideos);

        $images = Image::factory()->count(1)->create();
        $videos = Video::factory()->count(1)->create();

        $ids = collect($images)->merge($videos)->map(fn($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $response = $this
            ->jsonApi('videos')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(401);

        $this->assertDatabaseCount('image_post', 1);
        $this->assertDatabaseCount('post_video', 1);
    }

    public function testForbidden(): void
    {
        $existingImages = Image::factory()->count(1)->create();
        $existingVideos = Video::factory()->count(1)->create();

        $this->post->images()->saveMany($existingImages);
        $this->post->videos()->saveMany($existingVideos);

        $images = Image::factory()->count(1)->create();
        $videos = Video::factory()->count(1)->create();

        $ids = collect($images)->merge($videos)->map(fn($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('videos')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(403);

        $this->assertDatabaseCount('image_post', 1);
        $this->assertDatabaseCount('post_video', 1);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->accept($mediaType)
            ->withData([])
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(406);
    }

    public function testUnsupportedMediaType(): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->withData([])
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(415);
    }
}
