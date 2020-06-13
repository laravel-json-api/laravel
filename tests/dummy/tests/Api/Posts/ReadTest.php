<?php

namespace DummyApp\Tests\Api\Posts;

use DummyApp\Post;
use DummyApp\Tests\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = factory(Post::class)->create();

        $this->withoutExceptionHandling()
            ->jsonApi()
            ->get(url('/api/v1/posts', $post))
            ->assertSuccessful();
    }
}
