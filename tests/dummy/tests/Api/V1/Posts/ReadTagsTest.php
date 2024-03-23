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

use App\Models\Post;
use App\Models\Tag;
use App\Tests\Api\V1\TestCase;

class ReadTagsTest extends TestCase
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
        $tags = Tag::factory()->count(3)->create();
        $this->post->tags()->attach($tags);

        $expected = $tags
            ->map(fn(Tag $tag) => $this->serializer->tag($tag))
            ->all();

        $response = $this
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedMany($expected)->assertExactMeta([
            'count' => count($expected)
        ]);
    }

    public function testSort(): void
    {
        $tags = Tag::factory()
            ->count(3)
            ->create();

        $this->post->tags()->attach($tags);

        $expected = $this->identifiersFor('tags', $tags->sortByDesc('name'));

        $response = $this
            ->jsonApi('tags')
            ->sort('-name')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedManyInOrder($expected);
    }

    public function testWithCount(): void
    {
        $tags = Tag::factory()->count(3)->create();
        $this->post->tags()->attach($tags);

        $expected = $tags->map(fn(Tag $tag) => $this->serializer
            ->tag($tag)
            ->withRelationshipMeta('posts', ['count' => 1])
        )->all();

        $response = $this
            ->jsonApi('tags')
            ->query(['withCount' => 'posts'])
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedManyExact($expected);
    }

    public function testInvalidQueryParameter(): void
    {
        $response = $this
            ->jsonApi('tags')
            ->sort('-name', 'foo')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertExactErrorStatus([
            'detail' => 'Sort parameter foo is not allowed.',
            'source' => ['parameter' => 'sort'],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ]);
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
            ->get(url('/api/v1/posts', [$this->post, 'tags']))
            ->assertStatus(406);
    }
}
