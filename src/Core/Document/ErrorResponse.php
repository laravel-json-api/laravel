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

namespace LaravelJsonApi\Core\Document;

use Illuminate\Contracts\Support\Responsable;
use LaravelJsonApi\Contracts\Serializable;

class ErrorResponse implements Serializable, Responsable
{

    use Concerns\Serializable;

    /**
     * @var ErrorList
     */
    private ErrorList $errors;

    /**
     * @var int|null
     */
    private ?int $status = null;

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var int
     */
    private int $encodeOptions = 0;

    /**
     * ErrorResponse constructor.
     *
     * @param ErrorList|Error|Error[] $errors
     */
    public function __construct($errors)
    {
        $this->errors = ErrorList::cast($errors);
    }

    /**
     * Set JSON encode options.
     *
     * @param int $options
     * @return $this
     */
    public function withEncodeOptions(int $options): self
    {
        $this->encodeOptions = $options;

        return $this;
    }

    /**
     * Set response headers.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the response status.
     *
     * This overrides the default status, which is derived from
     * the error list.
     *
     * @param int $status
     * @return $this
     */
    public function withStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return response(
            $this->toJson($this->encodeOptions),
            $this->status ?: $this->errors->status(),
            $this->headers
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'errors' => $this->errors->toArray(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'errors' => $this->errors,
        ];
    }

}
