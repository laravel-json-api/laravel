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
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Core\Resolver\ResourceRequest as ResourceRequestResolver;
use LaravelJsonApi\Http\Exceptions\JsonApiException;
use LaravelJsonApi\Spec\ResourceBuilder;
use LaravelJsonApi\Spec\UnexpectedDocumentException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
     * @return bool
     */
    public function isCreating(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * @return bool
     */
    public function isUpdating(): bool
    {
        return $this->isMethod('PATCH');
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
        /** Content negotiation. */
        if (!$this->isSupportedMediaType()) {
            throw $this->unsupportedMediaType();
        }

        /** JSON API spec compliance. */
        $this->validateDocument();
    }

    /**
     * @return bool
     */
    protected function isSupportedMediaType(): bool
    {
        return $this->isJsonApi();
    }

    /**
     * Get an exception if the media type is not supported.
     *
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function unsupportedMediaType(): HttpExceptionInterface
    {
        return new HttpException(
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
            'The request entity has a media type which the server or resource does not support.'
        );
    }

    /**
     * Get an exception if the JSON is invalid.
     *
     * @param \JsonException $ex
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function invalidJson(\JsonException $ex): HttpExceptionInterface
    {
        return new JsonApiException(Error::make()
            ->setStatus(Response::HTTP_BAD_REQUEST)
            ->setCode($ex->getCode())
            ->setTitle('Invalid JSON')
            ->setDetail($ex->getMessage())
        );
    }

    /**
     * Get an exception if the JSON is not an object.
     *
     * @param UnexpectedDocumentException $ex
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function unexpectedDocument(UnexpectedDocumentException $ex): HttpExceptionInterface
    {
        return new JsonApiException(Error::make()
            ->setStatus(Response::HTTP_BAD_REQUEST)
            ->setTitle('Invalid JSON')
            ->setDetail($ex->getMessage())
        );
    }

    /**
     * Get an exception for a JSON document that has failed JSON-API specification validation.
     *
     * @param ErrorList $errors
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function invalidDocument(ErrorList $errors): HttpExceptionInterface
    {
        return new JsonApiException($errors);
    }

    /**
     * Validate the JSON API document.
     *
     * @return void
     * @throws HttpExceptionInterface
     */
    private function validateDocument(): void
    {
        $route = JsonApi::route();
        $id = $this->isUpdating() ? $route->resourceId() : null;

        /** @var ResourceBuilder $builder */
        $builder = app(ResourceBuilder::class);

        try {
            $document = $builder
                ->expects($route->resourceType(), $id)
                ->build($this->getContent());
        } catch (\JsonException $ex) {
            throw $this->invalidJson($ex);
        } catch (UnexpectedDocumentException $ex) {
            throw $this->unexpectedDocument($ex);
        }

        if ($document->invalid()) {
            throw $this->invalidDocument($document->errors());
        }
    }

}
