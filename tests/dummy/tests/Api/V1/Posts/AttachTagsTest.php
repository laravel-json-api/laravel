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

        $ids = $this->identifiersFor('tags', $tags);

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
            'source' => ['pointer' => '/data/0'],
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

        $ids = $this->identifiersFor('tags', $tags);

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

        $ids = $this->identifiersFor('tags', $tags);

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('tags')
            ->withData($ids)
            ->post(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

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
            ->accept($mediaType)
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
                'id' => $tag->getRouteKey(),
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
