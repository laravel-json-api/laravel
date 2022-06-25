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
use App\Models\Tag;
use App\Models\User;
use App\Tests\Api\V1\TestCase;
use Illuminate\Database\Eloquent\Collection;

class UpdateTagsTest extends TestCase
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
        $tags->push($existing[1]);

        $ids = $this->identifiersFor('tags', $tags);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('tags')
            ->withData($ids)
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedToMany($ids);

        $this->assertSame(3, $this->post->tags()->count());

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->getKey(),
                'taggable_id' => $this->post->getKey(),
                'taggable_type' => Post::class,
            ]);
        }
    }

    public function testClear(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('tags')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedNone();

        $this->assertDatabaseMissing('taggables', [
            'taggable_id' => $this->post->getKey(),
            'taggable_type' => Post::class,
        ]);
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
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertExactErrorStatus([
            'detail' => 'The tags field must be a to-many relationship containing tags resources.',
            'source' => ['pointer' => '/data/0'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    public function testUnauthorized(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        $response = $this
            ->jsonApi('tags')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(401);

        $this->assertDatabaseCount('taggables', 2);
    }

    public function testForbidden(): void
    {
        $existing = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($existing);

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('tags')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(403);

        $this->assertDatabaseCount('taggables', 2);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->accept($mediaType)
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(406);
    }

    public function testUnsupportedMediaType(): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->withData([])
            ->patch(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertStatus(415);
    }
}
