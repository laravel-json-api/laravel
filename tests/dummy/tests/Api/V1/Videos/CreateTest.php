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

namespace App\Tests\Api\V1\Videos;

use App\Models\Tag;
use App\Models\Video;
use App\Tests\Api\V1\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaravelJsonApi\Core\Document\ResourceObject;

class CreateTest extends TestCase
{

    /**
     * @var EloquentCollection
     */
    private EloquentCollection $tags;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tags = Tag::factory()->count(3)->create();
    }

    public function test(): void
    {
        $video = Video::factory()->make();
        $data = $this->serialize($video);

        $expected = $data
            ->forget('createdAt', 'updatedAt');

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($user = $video->owner)
            ->jsonApi('videos')
            ->withData($data)
            ->includePaths('tags')
            ->post('/api/v1/videos');

        $id = $response
            ->assertCreatedWithServerId(url('/api/v1/videos'), $expected)
            ->id();

        $this->assertDatabaseHas('videos', [
            'uuid' => $id,
            'owner_id' => $user->getKey(),
            'title' => $data['title'],
            'url' => $data['url'],
        ]);

        $this->assertDatabaseCount('taggables', 2);

        /** @var Tag $tag */
        foreach ($this->tags->take(2) as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->getKey(),
                'taggable_id' => $id,
                'taggable_type' => Video::class,
            ]);
        }
    }

    public function testClientId(): void
    {
        $video = Video::factory()->make();

        $data = $this
            ->serialize($video)
            ->withId($id = '81166677-f3c4-440c-9a4a-12b89802d731');

        $expected = $data
            ->forget('createdAt', 'updatedAt');

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($video->owner)
            ->jsonApi('videos')
            ->withData($data)
            ->includePaths('tags')
            ->post('/api/v1/videos');

        $response->assertCreatedWithClientId(url('/api/v1/videos'), $expected);

        $this->assertDatabaseHas('videos', [
            'uuid' => $id,
            'title' => $data['title'],
            'url' => $data['url'],
        ]);

        $this->assertDatabaseCount('taggables', 2);
    }

    public function testClientIdAlreadyExists(): void
    {
        $video = Video::factory()->make();
        $existing = Video::factory()->create();

        $data = $this
            ->serialize($video)
            ->withId($id = $existing->getRouteKey());

        $response = $this
            ->actingAs($video->owner)
            ->jsonApi('videos')
            ->withData($data)
            ->post('/api/v1/videos');

        $response->assertExactErrorStatus([
            'detail' => "Resource {$id} already exists.",
            'source' => ['pointer' => '/data/id'],
            'status' => '409',
            'title' => 'Conflict',
        ]);
    }

    public function testInvalidClientId(): void
    {
        $video = Video::factory()->make();

        $data = $this
            ->serialize($video)
            ->withId('123456');

        $response = $this
            ->actingAs($video->owner)
            ->jsonApi('videos')
            ->withData($data)
            ->post('/api/v1/videos');

        $response->assertExactErrorStatus([
            'detail' => 'The id format is invalid.',
            'source' => ['pointer' => '/data/id'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    /**
     * Serialize the video model for a valid create request.
     *
     * @param Video $video
     * @return ResourceObject
     */
    private function serialize(Video $video): ResourceObject
    {
        return ResourceObject::fromArray([
            'type' => 'videos',
            'attributes' => [
                'createdAt' => null,
                'title' => $video->title,
                'updatedAt' => null,
                'url' => $video->url,
            ],
            'relationships' => [
                'tags' => [
                    'data' => $this->identifiersFor('tags', $this->tags->take(2)),
                ],
            ],
        ]);
    }
}
