<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace App\Tests\Api\V1\Posts\Actions;

use App\Models\Post;
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class PublishTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create(['published_at' => null]);
    }

    public function test(): void
    {
        $this->travelTo($date = now()->milliseconds(0));

        $expected = $this->serializer
            ->post($this->post)
            ->replace('publishedAt', $date)
            ->replace('author', ['type' => 'users', 'id' => $this->post->author]);

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->includePaths('author')
            ->post(url('/api/v1/posts', [$this->post, '-actions/publish']));

        $response->assertFetchedOneExact($expected);
        $response->assertIncluded([$expected['author']]);

        $this->assertDatabaseHas('posts', array_replace(
            $this->post->getRawOriginal(),
            ['published_at' => $date->toDateTimeString()]
        ));
    }

    public function testAlreadyPublished(): void
    {
        $this->post->update(['published_at' => now()]);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->post(url('/api/v1/posts', [$this->post, '-actions/publish']));

        $response->assertExactErrorStatus([
            'detail' => 'Post is already published.',
            'status' => '403',
            'title' => 'Forbidden',
        ]);

        $this->assertDatabaseHas('posts', $this->post->getRawOriginal());
    }

    public function testUnauthorized(): void
    {
        $response = $this
            ->jsonApi('posts')
            ->contentType('application/json')
            ->post(url('/api/v1/posts', [$this->post, '-actions/publish']));

        $response->assertNotFound();

        $this->assertDatabaseHas('posts', $this->post->getRawOriginal());
    }

    public function testForbidden(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('posts')
            ->contentType('application/json')
            ->post(url('/api/v1/posts', [$this->post, '-actions/publish']));

        $response->assertNotFound();

        $this->assertDatabaseHas('posts', $this->post->getRawOriginal());
    }
}
