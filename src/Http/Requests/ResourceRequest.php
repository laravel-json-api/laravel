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

namespace LaravelJsonApi\Http\Requests;

use Illuminate\Http\Response;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Resolver\ResourceRequest as ResourceRequestResolver;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResourceRequest extends FormRequest
{

    /**
     * @var callable|null
     */
    private static $requestResolver;

    /**
     * Specify the callback to use to guess the request class for a JSON API resource.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessResourceRequestUsing(callable $resolver): void
    {
        self::$requestResolver = $resolver;
    }

    /**
     * Resolve the request instance for the specified resource type.
     *
     * @param string $resourceType
     * @return ResourceRequest
     */
    public static function forResource(string $resourceType): ResourceRequest
    {
        $resolver = self::$requestResolver ?: new ResourceRequestResolver('Request');

        return $resolver($resourceType);
    }

    /**
     * @inheritDoc
     */
    public function validationData()
    {
        $data = $this->json('data');

        if (is_array($data)) {
            return ResourceObject::fromArray($data)->all();
        }

        throw new LogicException('Expecting data to be an array.');
    }

    /**
     * @inheritDoc
     */
    protected function prepareForValidation()
    {
        if (!$this->isSupportedMediaType()) {
            throw $this->unsupportedMediaType();
        }
    }

    /**
     * @return bool
     */
    protected function isSupportedMediaType(): bool
    {
        return $this->isJsonApi();
    }

    /**
     * @return HttpException
     * @todo add translation
     */
    protected function unsupportedMediaType(): HttpException
    {
        return new HttpException(
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
            'The request entity has a media type which the server or resource does not support.'
        );
    }
}
