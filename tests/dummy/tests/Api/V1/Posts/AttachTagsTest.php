<?php
/*
 * Copyright 2020 Cloud Creativity Limited
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
use App\Models\Tag;
use App\Models\User;
use App\Tests\Api\V1\TestCase;
use Illuminate\Database\Eloquent\Collection;

class AttachTagsTest extends TestCase
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
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        /** @var Collection $tags */
        $tags = Tag::factory()->count(2)->create();

        $ids = $tags
            ->map(fn(Tag $tag) => ['type' => 'tags', 'id' => (string) $tag->getRouteKey()])
            ->all();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('tags')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertNoContent();

        $this->assertSame($existing->count() + $tags->count(), $this->post->tags()->count());

        /** @var Tag $tag */
        foreach ($existing->merge($tags) as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->getKey(),
                'taggable_id' => $this->post->getKey(),
                'taggable_type' => Post::class,
            ]);
        }
    }

    public function testInvalid(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            [
                'type' => 'comments',
                'id' => (string) $comment->getRouteKey(),
            ],
        ];

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('tags')
            ->withData($data)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertExactErrorStatus([
            'detail' => 'The tags field must be a to-many relationship containing tags resources.',
            'source' => ['pointer' => '/data/relationships/tags'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    public function testUnauthorized(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        /** @var Collection $tags */
        $tags = Tag::factory()->count(2)->create();

        $ids = $tags
            ->map(fn(Tag $tag) => ['type' => 'tags', 'id' => (string) $tag->getRouteKey()])
            ->all();

        $response = $this
            ->jsonApi('tags')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(401);

        $this->assertDatabaseCount('taggables', 2);
    }

    public function testForbidden(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        /** @var Collection $tags */
        $tags = Tag::factory()->count(2)->create();

        $ids = $tags
            ->map(fn(Tag $tag) => ['type' => 'tags', 'id' => (string) $tag->getRouteKey()])
            ->all();

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('tags')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(403);

        $this->assertDatabaseCount('taggables', 2);
    }

    public function testNotAcceptableMediaType(): void
    {
        $tag = Tag::factory()->create();

        $data = [
            [
                'type' => 'tags',
                'id' => (string) $tag->getRouteKey(),
            ],
        ];

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->accept('text/html')
            ->withData($data)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(406);
    }

    public function testUnsupportedMediaType(): void
    {
        $tag = Tag::factory()->create();

        $data = [
            [
                'type' => 'tags',
                'id' => (string) $tag->getRouteKey(),
            ],
        ];

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->withData($data)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(415);
    }
}
