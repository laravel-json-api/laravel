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
use App\Models\Post;
use App\Tests\Api\V1\TestCase;
use Illuminate\Support\Arr;

class ReadCommentsTest extends TestCase
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
        $comments = Comment::factory()
            ->count(3)
            ->create(['post_id' => $this->post]);

        $expected = $this->identifiersFor('comments', $comments);

        Comment::factory()
            ->for(Post::factory())
            ->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->get($self = url('/api/v1/posts', [$this->post, 'comments']));

        $response->assertFetchedMany($expected)
            ->assertLinks(['self' => $self])
            ->assertExactMeta(['count' => 3]);
    }

    public function testPaginated(): void
    {
        $comments = Comment::factory()
            ->count(5)
            ->create(['post_id' => $this->post]);

        $expected = $this->identifiersFor(
            'comments',
            $comments->sortBy('id')->take(3)
        );

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->page(['number' => '1', 'size' => '3'])
            ->sort('id')
            ->get($url = url('/api/v1/posts', [$this->post, 'comments']));

        $response->assertFetchedMany($expected)->assertExactMeta([
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
            'first' => $url . '?' . Arr::query(['page' => ['number' => 1, 'size' => 3], 'sort' => 'id']),
            'last' => $url . '?' . Arr::query(['page' => ['number' => 2, 'size' => 3], 'sort' => 'id']),
            'next' => $url . '?' . Arr::query(['page' => ['number' => 2, 'size' => 3], 'sort' => 'id']),
        ]);
    }

    public function testFilter(): void
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
            ->get(url('/api/v1/posts', [$this->post, 'comments']));

        $response->assertFetchedMany(
            $this->identifiersFor('comments', $expected)
        );
    }

    public function testIncludePath(): void
    {
        $comments = Comment::factory()
            ->count(2)
            ->create(['post_id' => $this->post]);

        $expected = $this->identifiersFor('comments', $comments);

        $response = $this
            ->jsonApi('comments')
            ->includePaths('user')
            ->get(url('/api/v1/posts', [$this->post, 'comments']));

        $response->assertFetchedMany($expected)->assertIncluded(
            $this->identifiersFor('users', $comments->pluck('user')->all())
        );
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
            ->get(url('/api/v1/posts', [$this->post, 'comments']))
            ->assertStatus(406);
    }
}
