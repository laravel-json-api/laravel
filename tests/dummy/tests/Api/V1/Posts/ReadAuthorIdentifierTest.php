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

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class ReadAuthorIdentifierTest extends TestCase
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
        $this->post = Post::factory()
            ->for(User::factory(['email' => 'john.doe@example.com']), 'author')
            ->create();
    }

    public function test(): void
    {
        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('users')
            ->get($self = url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertExactJson([
            'links' => [
                'self' => $self,
                'related' => url('/api/v1/posts', [$this->post, 'author']),
            ],
            'data' => [
                'type' => 'users',
                'id' => (string) $this->post->author->getRouteKey(),
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ]);
    }

    public function testFilterMatches(): void
    {
        $response = $this
            ->jsonApi('users')
            ->filter(['email' => $this->post->author->email])
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertFetchedToOne(
            $this->post->author
        );
    }

    public function testFilterDoesntMatch(): void
    {
        $response = $this
            ->jsonApi('users')
            ->filter(['email' => 'foo@bar.com'])
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertFetchedNull();
    }


    /**
     * Draft posts do not appear in our API for guests, because of our
     * post scope. Therefore, attempting to access a draft post as a
     * guest should receive a 404 response.
     */
    public function testDraftAsGuest(): void
    {
        $this->post->update(['published_at' => null]);

        $response = $this
            ->jsonApi('users')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertStatus(404);
    }

    /**
     * Same if an authenticated user attempts to access the
     * draft post when they are not the author - they would receive
     * a 404 as it is excluded from the API.
     */
    public function testDraftUserIsNotAuthor(): void
    {
        $this->post->update(['published_at' => null]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('users')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertStatus(404);
    }

    /**
     * The author should be able to access their draft post.
     */
    public function testDraftAsAuthor(): void
    {
        $this->post->update(['published_at' => null]);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('users')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']));

        $response->assertFetchedToOne(
            $this->post->author
        );
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $this
            ->jsonApi()
            ->accept($mediaType)
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'author']))
            ->assertStatus(406);
    }
}
