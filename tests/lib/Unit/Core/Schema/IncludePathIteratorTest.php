<?php
/**
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

namespace LaravelJsonApi\Tests\Unit\Core\Schema;

use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Schema\IncludePathIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IncludePathIteratorTest extends TestCase
{

    /**
     * @var Container|MockObject
     */
    private Container $schemas;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schemas = $this->createMock(Container::class);
        $this->schemas->method('schemaFor')->willReturnMap([
            ['posts', $post = $this->createMock(Schema::class)],
            ['users', $user = $this->createMock(Schema::class)],
            ['comments', $comment = $this->createMock(Schema::class)],
            ['user-profiles', $profile = $this->createMock(Schema::class)],
        ]);

        $post->method('relationships')->willReturn([
            $this->createRelation('author', 'users'),
            $this->createRelation('comments', 'comments'),
            $this->createIgnoredRelation(),
        ]);

        $user->method('relationships')->willReturn([
            $this->createRelation('posts', 'posts'),
            $this->createRelation('profile', 'user-profiles'),
            $this->createIgnoredRelation(),
        ]);

        $comment->method('relationships')->willReturn([
            $this->createRelation('user', 'users'),
            $this->createIgnoredRelation(),
        ]);

        $profile->method('relationships')->willReturn([$this->createIgnoredRelation()]);
    }

    /**
     * @return array[]
     */
    public function depthProvider(): array
    {
        return [
            [1, ['author', 'comments']],
            [2, [
                'author',
                'author.posts',
                'author.profile',
                'comments',
                'comments.user',
            ]],
            [3, [
                'author',
                'author.posts',
                'author.posts.author',
                'author.posts.comments',
                'author.profile',
                'comments',
                'comments.user',
                'comments.user.posts',
                'comments.user.profile',
            ]],
        ];
    }

    /**
     * @param int $depth
     * @param array $expected
     * @dataProvider depthProvider
     */
    public function test(int $depth, array $expected): void
    {
        $iterator = new IncludePathIterator(
            $this->schemas,
            $this->schemas->schemaFor('posts'),
            $depth
        );

        $this->assertSame($expected, $iterator->toArray());
    }

    /**
     * @param string $name
     * @param string $inverse
     * @return Relation|MockObject
     */
    private function createRelation(string $name, string $inverse): Relation
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('isIncludePath')->willReturn(true);
        $relation->method('name')->willReturn($name);
        $relation->method('inverse')->willReturn($inverse);

        return $relation;
    }

    /**
     * @return Relation
     */
    private function createIgnoredRelation(): Relation
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('isIncludePath')->willReturn(false);
        $relation->expects($this->never())->method('name');
        $relation->expects($this->never())->method('inverse');

        return $relation;
    }
}
