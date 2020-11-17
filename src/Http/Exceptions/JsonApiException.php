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

namespace LaravelJsonApi\Http\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use LaravelJsonApi\Contracts\ErrorProvider;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Core\Responses\Concerns\IsResponsable;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class JsonApiException extends Exception implements HttpExceptionInterface, Responsable
{

    use IsResponsable;

    /**
     * @var ErrorList
     */
    private ErrorList $errors;

    /**
     * Fluent constructor.
     *
     * @param ErrorList|Error $errors
     * @param Throwable|null $previous
     * @return static
     */
    public static function make($errors, Throwable $previous = null): self
    {
        return new self($errors, $previous);
    }

    /**
     * JsonApiException constructor.
     *
     * @param ErrorList|ErrorProvider|Error|Error[] $errors
     * @param Throwable|null $previous
     * @param array $headers
     */
    public function __construct($errors, Throwable $previous = null, array $headers = [])
    {
        parent::__construct('JSON API error', 0, $previous);
        $this->errors = ErrorList::cast($errors);
        $this->withHeaders($headers);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return $this->errors->status();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return ErrorList
     */
    public function getErrors(): ErrorList
    {
        return $this->errors;
    }

    /**
     * @param $request
     * @return ErrorResponse
     */
    public function prepareResponse($request): ErrorResponse
    {
        return $this->errors
            ->prepareResponse($request)
            ->withJsonApi($this->jsonApi())
            ->withMeta($this->meta)
            ->withLinks($this->links)
            ->withHeaders($this->headers);
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return $this
            ->prepareResponse($request)
            ->toResponse($request);
    }

}
