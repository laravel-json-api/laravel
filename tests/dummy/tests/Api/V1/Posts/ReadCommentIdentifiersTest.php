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
use App\Models\Post;
use App\Tests\Api\V1\TestCase;
use Illuminate\Support\Arr;

class ReadCommentIdentifiersTest extends TestCase
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
        $expected = Comment::factory()
            ->count(3)
            ->create(['post_id' => $this->post]);

        Comment::factory()
            ->for(Post::factory())
            ->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->get($self = url('/api/v1/posts', [$this->post, 'relationships', 'comments']));

        $response->assertExactJson([
            'links' => [
                'self' => $self,
                'related' => url('/api/v1/posts', [$this->post, 'comments']),
            ],
            'meta' => [
                'count' => 3,
            ],
            'data' => $this->identifiersFor('comments', $expected),
            'jsonapi' => [
                'version' => '1.0',
            ],
        ]);
    }

    public function testPaginated(): void
    {
        $comments = Comment::factory()
            ->count(5)
            ->create(['post_id' => $this->post]);

        $expected = $this->identifiersFor(
            'comments',
            $comments->toBase()->sortBy('id')->take(3)
        );

        Comment::factory()
            ->for(Post::factory())
            ->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->page(['number' => '1', 'size' => '3'])
            ->sort('id')
            ->get($self = url('/api/v1/posts', [$this->post, 'relationships', 'comments']));

        $response->assertFetchedToManyInOrder($expected)->assertExactMeta([
            'count' => 5,
            'page' => [
                'currentPage' => 1,
                'from' => 1,
                'lastPage' => 2,
                'perPage' => 3,
                'to' => 3,
                'total' => 5,
            ],
        ])->assertLinks([
            'first' => $self . '?' . Arr::query(['page' => ['number' => 1, 'size' => 3], 'sort' => 'id']),
            'last' => $self . '?' . Arr::query(['page' => ['number' => 2, 'size' => 3], 'sort' => 'id']),
            'next' => $self . '?' . Arr::query(['page' => ['number' => 2, 'size' => 3], 'sort' => 'id']),
            'related' => url('/api/v1/posts', [$this->post, 'comments']),
            'self' => $self,
        ]);
    }

    public function testFiltered(): void
    {
        $comments = Comment::factory()
            ->count(4)
            ->create(['post_id' => $this->post]);

        $expected = $comments->take(2);

        $ids = $expected
            ->map(fn(Comment $comment) => $comment->getRouteKey())
            ->all();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->filter(['id' => $ids])
            ->get($self = url('/api/v1/posts', [$this->post, 'relationships', 'comments']));

        $response->assertFetchedToMany(
            $this->identifiersFor('comments', $expected)
        );
    }

    public function testInvalidMediaType(): void
    {
        $this->jsonApi()
            ->accept('text/html')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'comments']))
            ->assertStatus(406);
    }
}
