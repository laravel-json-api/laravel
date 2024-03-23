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

class ReadTagIdentifiersTest extends TestCase
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
        $tags = Tag::factory()
            ->count(3)
            ->create();

        $this->post->tags()->attach($tags);

        $expected = $this->identifiersFor('tags', $tags);

        $response = $this
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedToMany($expected)->assertExactMeta([
            'count' => 3,
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
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedToManyInOrder($expected);
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
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']))
            ->assertStatus(406);
    }
}
