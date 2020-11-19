<?php
/*
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

namespace LaravelJsonApi\Laravel\Exceptions;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Validation\Factory;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionParser
{

    /**
     * Get an exception renderer closure.
     *
     * @return Closure
     */
    public static function renderer(): Closure
    {
        return static function (Throwable $ex, $request) {
            return static::make()->render($request, $ex);
        };
    }

    /**
     * Fluent constructor.
     *
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * Render the exception, if the request wants the JSON API media type.
     *
     * @param Request $request
     * @param Throwable $ex
     * @return Response|mixed
     */
    public function render($request, Throwable $ex)
    {
        if ($this->isRenderable($request, $ex)) {
            return $this
                ->parse($request, $ex)
                ->toResponse($request);
        }

        return null;
    }

    /**
     * Does the HTTP request require a JSON API error response?
     *
     * This method determines if we need to render a JSON API error response
     * for the client. We need to do this if the client has requested JSON
     * API via its Accept header.
     *
     * @param Request $request
     * @param Throwable $e
     * @return bool
     */
    public function isRenderable($request, Throwable $e): bool
    {
        if ($e instanceof JsonApiException) {
            return true;
        }

        $acceptable = $request->getAcceptableContentTypes();

        return isset($acceptable[0]) && 'application/vnd.api+json' === $acceptable[0];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $ex
     * @return ErrorResponse
     */
    public function parse($request, Throwable $ex): ErrorResponse
    {
        if ($ex instanceof JsonApiException) {
            return $ex->prepareResponse($request);
        }

        if ($ex instanceof HttpExceptionInterface) {
            $response = new ErrorResponse($this->getHttpError($ex));
            return $response->withHeaders($ex->getHeaders());
        }

        if ($ex instanceof ValidationException) {
            return new ErrorResponse($this->getValidationErrors($ex));
        }

        return new ErrorResponse($this->getDefaultError());
    }

    /**
     * Convert a validation exception to JSON API errors.
     *
     * @param ValidationException $ex
     * @return iterable|mixed
     */
    protected function getValidationErrors(ValidationException $ex): iterable
    {
        return $this->factory()->createErrors($ex->validator);
    }

    /**
     * Convert a HTTP exception to a JSON API error.
     *
     * @param HttpExceptionInterface $e
     * @return Error
     */
    protected function getHttpError(HttpExceptionInterface $e): Error
    {
        return Error::make()
            ->setStatus($status = $e->getStatusCode())
            ->setTitle($this->getHttpTitle($status))
            ->setDetail(__($e->getMessage()));
    }

    /**
     * Convert a HTTP status code to a human-readable title.
     *
     * @param int|null $status
     * @return string|null
     */
    protected function getHttpTitle(?int $status): ?string
    {
        if ($status && isset(Response::$statusTexts[$status])) {
            return __(Response::$statusTexts[$status]);
        }

        return null;
    }

    /**
     * Get the default JSON API error.
     *
     * @return Error
     */
    protected function getDefaultError(): Error
    {
        return Error::make()
            ->setStatus(500)
            ->setTitle($this->getHttpTitle(500));
    }

    /**
     * Get the validation error factory.
     *
     * @return Factory
     */
    protected function factory(): Factory
    {
        return app(Factory::class);
    }
}
