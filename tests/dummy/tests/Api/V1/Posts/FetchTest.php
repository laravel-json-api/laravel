<?php

declare(strict_types=1);

namespace DummyApp\Tests\Api\V1\Posts;

use DummyApp\Post;
use DummyApp\Tests\Api\V1\TestCase;

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
}
