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

class HttpNotAcceptableException extends HttpException
{
    /**
     * HttpNotAcceptableException constructor.
     *
     * @param string|null $message
     * @param Throwable|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(
        ?string $message = null,
        ?Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        if (null === $message) {
            $message = __("The requested resource is capable of generating only content not acceptable "
                . "according to the Accept headers sent in the request.");
        }

        parent::__construct(Response::HTTP_NOT_ACCEPTABLE, $message, $previous, $headers, $code);
    }
}