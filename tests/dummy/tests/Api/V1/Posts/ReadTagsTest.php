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
            ->map(fn(Tag $tag) => $this->serializer->tag($tag)->jsonSerialize())
            ->all();

        $response = $this
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedMany($expected);
    }

    public function testSort(): void
    {
        $tags = Tag::factory()
            ->count(3)
            ->create();

        $this->post->tags()->attach($tags);

        $response = $this
            ->jsonApi('tags')
            ->sort('-name')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedManyInOrder(
            $tags->sortByDesc('name')
        );
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

    public function testInvalidMediaType(): void
    {
        $this->jsonApi()
            ->accept('text/html')
            ->get(url('/api/v1/posts', [$this->post, 'tags']))
            ->assertStatus(406);
    }
}
