<?php

declare(strict_types=1);

namespace DummyApp\Tests\Api\V1\Posts;

use DummyApp\Post;
use DummyApp\Tests\Api\V1\TestCase;
use Illuminate\Support\Arr;

class FetchTest extends TestCase
{

    public function test(): void
    {
        $posts = factory(Post::class, 3)->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedMany($posts);
    }

    public function testPaginated(): void
    {
        $posts = factory(Post::class, 5)->create();

        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'per_page' => 3,
            'to' => 3,
            'total' => 5,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 1, 'size' => 3]]),
            'next' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 2, 'size' => 3]]),
            'last' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 2, 'size' => 3]]),
        ];

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->page(['number' => 1, 'size' => 3])
            ->get('/api/v1/posts');

        $response->assertFetchedMany($posts->take(3))
            ->assertMeta($meta)
            ->assertLinks($links);
    }
}
