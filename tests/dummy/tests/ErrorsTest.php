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

namespace DummyApp\Tests;

use Illuminate\Testing\TestResponse;

class ErrorsTest extends TestCase
{

    public function testInvalidJson(): void
    {
        $json = <<<JSON
{
    "data": {
        "type": "posts"
    }
JSON;

        $response = $this->sendInvalid('POST', '/api/v1/posts', $json);

        $response->assertStatus(400)->assertExactJson([
            'errors' => [
                [
                    'code' => '4',
                    'detail' => 'Syntax error',
                    'status' => '400',
                    'title' => 'Invalid JSON',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ]);
    }

    /**
     * Valid JSON, but it is not an object.
     */
    public function testUnexpectedDocument(): void
    {
        $response = $this->sendInvalid('POST', '/api/v1/posts', '[]');

        $response->assertStatus(400)->assertExactJson([
            'errors' => [
                [
                    'detail' => 'Expecting JSON to decode to an object.',
                    'status' => '400',
                    'title' => 'Invalid JSON',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $content
     * @return TestResponse
     */
    private function sendInvalid(string $method, string $uri, string $content): TestResponse
    {
        $headers = $this->transformHeadersToServerVars([
            'Accept' => 'application/vnd.api+json',
            'CONTENT_TYPE' => 'application/vnd.api+json',
        ]);

        return $this->call($method, $uri, [], [], [], $headers, $content);
    }
}
