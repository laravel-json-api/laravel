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

use App\Models\Image;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Video;
use App\Tests\Api\V1\TestCase;

class ReadMediaTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $images = Image::factory()->count(2)->create();
        $videos = Video::factory()->count(2)->create();

        $this->post->images()->saveMany($images);
        $this->post->videos()->saveMany($videos);

        $expected = collect($images)->merge($videos)->map(fn ($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('videos') // @TODO the test assertion should work without having to do this.
            ->get(url('/api/v1/posts', [$this->post, 'media']));

        $response->assertFetchedMany($expected);
    }

    public function testIncludePath(): void
    {
        $images = Image::factory()->count(2)->create();
        $videos = Video::factory()->count(2)->create();

        $this->post->images()->saveMany($images);
        $this->post->videos()->saveMany($videos);

        $expected = collect($images)->merge($videos)->map(fn ($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $tag->videos()->save($video = $videos[0]);

        $response = $this
            ->jsonApi('videos') // @TODO should be able to remove this
            ->includePaths('tags')
            ->get(url('/api/v1/posts', [$this->post, 'media']));

        $response->assertFetchedMany($expected)->assertIncluded([
            ['type' => 'tags', 'id' => $tag->getRouteKey()],
        ]);
    }

    public function testFilter1(): void
    {
        $images = Image::factory()->count(3)->create();
        $videos = Video::factory()->count(3)->create();

        $this->post->images()->saveMany($images);
        $this->post->videos()->saveMany($videos);

        $expected = collect([$images[1], $videos[2]]);

        $ids = $expected->map(fn($model) => (string) $model->getRouteKey());

        $response = $this
            ->jsonApi('videos')
            ->filter(['id' => $ids])
            ->get(url('/api/v1/posts', [$this->post, 'media']));

        $response->assertFetchedMany($expected->map(fn ($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all());
    }

    public function testFilter2(): void
    {
        $images = Image::factory()->count(3)->create();
        $videos = Video::factory()->count(3)->create();

        $this->post->images()->saveMany($images);
        $this->post->videos()->saveMany($videos);

        $expected = $images->take(2);

        $ids = $expected->map(fn($model) => (string) $model->getRouteKey());

        $response = $this
            ->jsonApi('videos')
            ->filter(['id' => $ids])
            ->get(url('/api/v1/posts', [$this->post, 'media']));

        $response->assertFetchedMany($expected->map(fn (Image $model) => [
            'type' => 'images',
            'id' => (string) $model->getRouteKey(),
        ])->all());
    }

    public function testWithCount(): void
    {
        $images = Image::factory()->count(1)->create();

        $videos = Video::factory()
            ->has(Tag::factory()->count(1))
            ->count(1)
            ->create();

        $this->post->images()->saveMany($images);
        $this->post->videos()->saveMany($videos);

        $expectedImages = $images->toBase()->map(fn(Image $image) => $this->serializer
            ->image($image)
        )->all();

        $expectedVideos = $videos->toBase()->map(fn(Video $video) => $this->serializer
            ->video($video)
            ->withRelationshipMeta('tags', ['count' => 1])
        )->all();

        $expected = collect($expectedImages)->merge($expectedVideos)->all();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('videos') // @TODO the test assertion should work without having to do this.
            ->query(['withCount' => 'tags'])
            ->get(url('/api/v1/posts', [$this->post, 'media']));

        $response->assertFetchedManyExact($expected);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $this->jsonApi()
            ->accept($mediaType)
            ->get(url('/api/v1/posts', [$this->post, 'media']))
            ->assertStatus(406);
    }
}
