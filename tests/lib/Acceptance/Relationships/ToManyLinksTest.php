<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Acceptance\Relationships;

use App\JsonApi\V1\Posts\PostSchema;
use App\Models\Post;
use App\Models\Tag;
use Closure;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Laravel\Tests\Acceptance\TestCase;
use function url;

class ToManyLinksTest extends TestCase
{

    /**
     * @var PostSchema
     */
    private PostSchema $schema;

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @var Tag
     */
    private Tag $tag;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        JsonApiRoute::server('v1')->prefix('api/v1')->resources(function ($server) {
            $server->resource('posts', JsonApiController::class)->relationships(function ($relationships) {
                $relationships->hasMany('tags');
            });
        });

        $this->schema = JsonApi::server('v1')->schemas()->schemaFor('posts');
        $this->post = Post::factory()->create();
        $this->post->tags()->attach($this->tag = Tag::factory()->create());
    }

    /**
     * @return array[]
     */
    public static function relationshipProvider(): array
    {
        return [
            'hidden' => [
                static function (PostSchema $schema) {
                    $schema->relationship('tags')->hidden();
                    return null;
                },
            ],
            'no links' => [
                static function (PostSchema $schema) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutLinks()
                    );
                    return null;
                },
            ],
            'no self link' => [
                static function (PostSchema $schema, Post $post) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutSelfLink()
                    );
                    return ['related' => url('/api/v1/posts', [$post, 'tags'])];
                },
            ],
            'no related link' => [
                static function (PostSchema $schema, Post $post) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutRelatedLink()
                    );
                    return ['self' => url('/api/v1/posts', [$post, 'relationships', 'tags'])];
                },
            ],
        ];
    }

    /**
     * @param Closure $scenario
     * @return void
     * @dataProvider relationshipProvider
     */
    public function testRelationship(Closure $scenario): void
    {
        $expected = $scenario($this->schema, $this->post);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedToMany([$this->tag]);

        if (is_array($expected)) {
            $response->assertLinks($expected);
        }
    }


    /**
     * @return array[]
     */
    public static function relatedProvider(): array
    {
        return [
            'hidden' => [
                static function (PostSchema $schema) {
                    $schema->relationship('tags')->hidden();
                    return null;
                },
            ],
            'no links' => [
                static function (PostSchema $schema) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutLinks()
                    );
                    return null;
                },
            ],
            'no self link' => [
                static function (PostSchema $schema, Post $post) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutSelfLink()
                    );
                    // related becomes self.
                    return ['self' => url('/api/v1/posts', [$post, 'tags'])];
                },
            ],
            'no related link' => [
                static function (PostSchema $schema, Post $post) {
                    $schema->relationship('tags')->serializeUsing(
                        static fn($relation) => $relation->withoutRelatedLink()
                    );
                    // related becomes self, but it's missing so we can't do that.
                    return null;
                },
            ],
        ];
    }

    /**
     * @param Closure $scenario
     * @return void
     * @dataProvider relatedProvider
     */
    public function testRelated(Closure $scenario): void
    {
        $expected = $scenario($this->schema, $this->post);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'tags']));

        $response->assertFetchedMany([$this->tag]);

        if (is_array($expected)) {
            $response->assertLinks($expected);
        }
    }

}
