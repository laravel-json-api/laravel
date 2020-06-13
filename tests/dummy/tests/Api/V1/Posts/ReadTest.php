<?php

namespace DummyApp\Tests\Api\V1\Posts;

use DummyApp\Post;
use DummyApp\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = factory(Post::class)->create();
        $expected = $this->serializer->post($post)->toArray();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOneExact($expected);
    }
}
