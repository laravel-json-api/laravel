<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\Tests\Api\V1\Users;

use App\Models\Comment;
use App\Models\Phone;
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class UpdatePhoneTest extends TestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test(): void
    {
        $existing = Phone::factory()->create(['user_id' => $this->user]);
        $new = Phone::factory()->create();

        $id = ['type' => 'phones', 'id' => (string) $new->getRouteKey()];

        $response = $this
            ->actingAs($this->user)
            ->jsonApi('phones')
            ->withData($id)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertFetchedToOne($id);

        $this->assertModelMissing($existing);
        $this->assertDatabaseHas($new, [
            'id' => $new->getKey(),
            'user_id' => $this->user->getKey(),
        ]);
    }

    public function testClear(): void
    {
        $phone = Phone::factory()->create(['user_id' => $this->user]);

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->user)
            ->jsonApi('phones')
            ->withData(null)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertFetchedNull();

        $this->assertModelMissing($phone);
    }

    public function testInvalid(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            'type' => 'comments',
            'id' => (string) $comment->getRouteKey(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->jsonApi('phones')
            ->withData($data)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertExactErrorStatus([
            'detail' => 'The phone field must be a to-one relationship containing phones resources.',
            'source' => ['pointer' => '/data'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    public function testUnauthorized(): void
    {
        $existing = Phone::factory()->create(['user_id' => $this->user]);

        $response = $this
            ->jsonApi('phones')
            ->withData(null)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertStatus(401);

        $this->assertModelExists($existing);
    }

    public function testForbidden(): void
    {
        $existing = Phone::factory()->create(['user_id' => $this->user]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('phones')
            ->withData(null)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertStatus(403);

        $this->assertModelExists($existing);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $response = $this
            ->actingAs($this->user)
            ->jsonApi('phones')
            ->accept($mediaType)
            ->withData(null)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertStatus(406);
    }

    public function testUnsupportedMediaType(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->jsonApi('phones')
            ->contentType('application/json')
            ->withData(null)
            ->patch(url('/api/v1/users', [$this->user, 'relationships', 'phone']));

        $response->assertStatus(415);
    }
}