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

namespace LaravelJsonApi\Laravel\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HttpUnsupportedMediaTypeException extends HttpException
{
    /**
     * HttpUnsupportedMediaTypeException constructor.
     *
     * @param string|null $message
     * @param Throwable|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(string $message = null, Throwable $previous = null, array $headers = [], int $code = 0)
    {
        if (null === $message) {
            $message = __('The request entity has a media type which the server or resource does not support.');
        }

        parent::__construct(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $previous, $headers, $code);
    }
}