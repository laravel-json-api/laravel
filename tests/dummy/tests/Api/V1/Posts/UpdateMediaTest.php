<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\Tests\Api\V1\Posts;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use App\Tests\Api\V1\TestCase;

class UpdateMediaTest extends TestCase
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

        $images = Image::factory()->count(1)->create()->push(
            $existingImages->first()
        );

        $videos = Video::factory()->count(1)->create()->push(
            $existingVideos->last()
        );

        $ids = collect($images)->merge($videos)->map(fn($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('videos') // @TODO assertions should work without this.
            ->withData($ids)
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertFetchedToMany($ids);

        $this->assertDatabaseCount('image_post', count($images));
        $this->assertDatabaseCount('post_video', count($videos));

        foreach ($images as $image) {
            $this->assertDatabaseHas('image_post', [
                'image_uuid' => $image->getKey(),
                'post_id' => $this->post->getKey(),
            ]);
        }

        foreach ($videos as $video) {
            $this->assertDatabaseHas('post_video', [
                'post_id' => $this->post->getKey(),
                'video_uuid' => $video->getKey(),
            ]);
        }
    }

    public function testClear(): void
    {
        $existingImages = Image::factory()->count(1)->create();
        $existingVideos = Video::factory()->count(1)->create();

        $this->post->images()->saveMany($existingImages);
        $this->post->videos()->saveMany($existingVideos);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('videos')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertFetchedNone();

        $this->assertDatabaseMissing('image_post', ['post_id' => $this->post->getKey()]);
        $this->assertDatabaseMissing('post_video', ['post_id' => $this->post->getKey()]);
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
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

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

        $response = $this
            ->jsonApi('videos')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

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

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('videos')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

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
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(406);
    }

    public function testUnsupportedMediaType(): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'media']));

        $response->assertStatus(415);
    }
}
