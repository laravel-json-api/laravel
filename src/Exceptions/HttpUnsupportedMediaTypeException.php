<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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