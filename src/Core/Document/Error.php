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

use LaravelJsonApi\Core\Contracts\Serializable;
use LogicException;
use function array_filter;

class Error implements Serializable
{

    use Concerns\HasLinks;
    use Concerns\HasMeta;
    use Concerns\Serializable;

    /**
     * @var mixed|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $detail;

    /**
     * @var ErrorSource|null
     */
    private $source;

    /**
     * Create a JSON API error object.
     *
     * @param Error|array $value
     * @return Error
     */
    public static function cast($value): Error
    {
        if ($value instanceof Error) {
            return $value;
        }

        if (is_array($value)) {
            return Error::fromArray($value);
        }

        throw new LogicException('Unexpected error value.');
    }

    /**
     * Create an error from an array.
     *
     * @param array $values
     * @return static
     */
    public static function fromArray(array $values): Error
    {
        $error = new static();

        return $error
            ->withId($values['id'] ?? null)
            ->withLinks($values['links'] ?? null)
            ->withStatus($values['status'] ?? null)
            ->withCode($values['code'] ?? null)
            ->withTitle($values['title'] ?? null)
            ->withDetail($values['detail'] ?? null)
            ->withSource($values['source'] ?? null)
            ->withMeta($values['meta'] ?? null);
    }

    /**
     * A unique identifier for this particular occurrence of the problem.
     *
     * @return mixed|null
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Add an id.
     *
     * @param mixed $id
     * @return $this
     */
    public function withId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Remove the id.
     *
     * @return $this
     */
    public function withoutId(): self
    {
        $this->id = null;

        return $this;
    }

    /**
     * Add an about link.
     *
     * @param string $href
     * @param null $meta
     * @return $this
     */
    public function withAboutLink(string $href, $meta = null): self
    {
        $this->links()->put('about', $href, $meta);

        return $this;
    }

    /**
     * Remove the about link.
     *
     * @return $this
     */
    public function withoutAboutLink(): self
    {
        $this->links()->forget('about');

        return $this;
    }

    /**
     * The HTTP status code applicable to this error.
     *
     * @return string|null
     */
    public function status(): ?string
    {
        return $this->status;
    }

    /**
     * Add an HTTP status.
     *
     * @param string|null $status
     * @return $this
     */
    public function withStatus(?string $status): self
    {
        $this->status = $status ?: null;

        return $this;
    }

    /**
     * Remove the HTTP status.
     *
     * @return $this
     */
    public function withoutStatus(): self
    {
        $this->status = null;

        return $this;
    }

    /**
     * The application-specific error code.
     *
     * @return string|null
     */
    public function code(): ?string
    {
        return $this->code;
    }

    /**
     * Add an application-specific error code.
     *
     * @param string|null $code
     * @return $this
     */
    public function withCode(?string $code): self
    {
        $this->code = $code ?: null;

        return $this;
    }

    /**
     * Remove the application-specific error code.
     *
     * @return $this
     */
    public function withoutCode(): self
    {
        $this->code = null;

        return $this;
    }

    /**
     * The short, human-readable summary of the problem.
     *
     * The title SHOULD NOT change from occurrence to occurrence of the problem,
     * except for purposes of localization.
     *
     * @return string|null
     */
    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * Add a short, human-readable summary of the problem.
     *
     * @param string|null $title
     * @return $this
     */
    public function withTitle(?string $title): self
    {
        $this->title = $title ?: null;

        return $this;
    }

    /**
     * Remove the title.
     *
     * @return $this
     */
    public function withoutTitle(): self
    {
        $this->title = null;

        return $this;
    }

    /**
     * The human-readable explanation specific to this occurrence of the problem.
     *
     * Like title, this field’s value can be localized.
     *
     * @return string|null
     */
    public function detail(): ?string
    {
        return $this->detail;
    }

    /**
     * Add a human-readable explanation of the error.
     *
     * @param string|null $detail
     * @return $this
     */
    public function withDetail(?string $detail): self
    {
        $this->detail = $detail ?: null;

        return $this;
    }

    /**
     * Remove the error detail.
     *
     * @return $this
     */
    public function withoutDetail(): self
    {
        $this->detail = null;

        return $this;
    }

    /**
     * The object containing references to the source of the error,
     *
     * @return ErrorSource
     */
    public function source(): ErrorSource
    {
        if ($this->source) {
            return $this->source;
        }

        return $this->source = new ErrorSource();
    }

    /**
     * Add the error source object.
     *
     * @param mixed|null $source
     * @return $this
     */
    public function withSource($source): self
    {
        $this->source = ErrorSource::cast($source);

        return $this;
    }

    /**
     * Add a source JSON pointer.
     *
     * @param string|null $pointer
     * @return $this
     */
    public function withSourcePointer(?string $pointer): self
    {
        $this->source()->withPointer($pointer);

        return $this;
    }

    /**
     * Add a source URI query parameter.
     *
     * @param string|null $parameter
     * @return $this
     */
    public function withSourceParameter(?string $parameter): self
    {
        $this->source()->withParameter($parameter);

        return $this;
    }

    /**
     * Remove the error source object.
     *
     * @return $this
     */
    public function withoutSource(): self
    {
        $this->source = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_filter([
            'code' => $this->code(),
            'detail' => $this->detail(),
            'id' => $this->id(),
            'links' => $this->links()->toArray() ?: null,
            'meta' => $this->meta()->toArray() ?: null,
            'source' => $this->source()->toArray() ?: null,
            'status' => $this->status(),
            'title' => $this->title(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_filter([
            'code' => $this->code(),
            'detail' => $this->detail(),
            'id' => $this->id(),
            'links' => $this->links()->jsonSerialize(),
            'meta' => $this->meta()->jsonSerialize(),
            'source' => $this->source()->jsonSerialize(),
            'status' => $this->status(),
            'title' => $this->title(),
        ]);
    }
}
