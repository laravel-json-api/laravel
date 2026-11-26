<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Acceptance;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use PHPUnit\Framework\Attributes\DataProvider;

class FormRequestModelHelpersTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!method_exists(BaseFormRequest::class, 'modelOrFail')) {
            $this->markTestSkipped('Laravel only provides the form request model helpers from 13.44.');
        }
    }

    /**
     * @return array<string, array{class-string<ResourceRequest|ResourceQuery>}>
     */
    public static function requestProvider(): array
    {
        return [
            'resource request' => [ResourceRequest::class],
            'resource query' => [ResourceQuery::class],
        ];
    }

    /**
     * @param class-string<ResourceRequest|ResourceQuery> $requestClass
     * @return void
     */
    #[DataProvider('requestProvider')]
    public function testModelWithKey(string $requestClass): void
    {
        $user = User::factory()->create();

        $request = $requestClass::create('/api/v1/posts', 'POST', [
            'author' => (string) $user->getKey(),
        ]);

        $this->assertTrue($user->is($request->model('author', User::class)));
        $this->assertTrue($user->is($request->modelOrFail('author', User::class)));
        $this->assertNull($request->model('missing', User::class));
    }

    /**
     * @param class-string<ResourceRequest|ResourceQuery> $requestClass
     * @return void
     */
    #[DataProvider('requestProvider')]
    public function testModelOrFailWithKeyThrowsWhenModelDoesNotExist(string $requestClass): void
    {
        $request = $requestClass::create('/api/v1/posts', 'POST', [
            'author' => '999',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $request->modelOrFail('author', User::class);
    }
}
