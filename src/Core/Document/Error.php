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

use InvalidArgumentException;
use LaravelJsonApi\Contracts\Serializable;
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
    private ?string $status = null;

    /**
     * @var string|null
     */
    private ?string $code = null;

    /**
     * @var string|null
     */
    private ?string $title = null;

    /**
     * @var string|null
     */
    private ?string $detail = null;

    /**
     * @var ErrorSource|null
     */
    private ?ErrorSource $source = null;

    /**
     * Fluent constructor.
     *
     * @return Error
     */
    public static function make(): Error
    {
        return new static();
    }

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
        return static::make()
            ->setId($values['id'] ?? null)
            ->setLinks($values['links'] ?? null)
            ->setStatus($values['status'] ?? null)
            ->setCode($values['code'] ?? null)
            ->setTitle($values['title'] ?? null)
            ->setDetail($values['detail'] ?? null)
            ->setSource($values['source'] ?? null)
            ->setMeta($values['meta'] ?? null);
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
    public function setId($id): self
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
    public function setAboutLink(string $href, $meta = null): self
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
     * @param string|int|null $status
     * @return $this
     */
    public function setStatus($status): self
    {
        if (!is_int($status) && !is_string($status) && !is_null($status)) {
            throw new InvalidArgumentException('Expecting an integer, string or null.');
        }

        $this->status = !is_null($status) ? strval($status) : null;

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
     * @param string|int|null $code
     * @return $this
     */
    public function setCode($code): self
    {
        if (!is_int($code) && !is_string($code) && !is_null($code)) {
            throw new InvalidArgumentException('Expecting an integer, string or null.');
        }

        $this->code = !is_null($code) ? strval($code) : null;

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
    public function setTitle(?string $title): self
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
    public function setDetail(?string $detail): self
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
    public function setSource($source): self
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
    public function setSourcePointer(?string $pointer): self
    {
        $this->source()->setPointer($pointer);

        return $this;
    }

    /**
     * Add a source URI query parameter.
     *
     * @param string|null $parameter
     * @return $this
     */
    public function setSourceParameter(?string $parameter): self
    {
        $this->source()->setParameter($parameter);

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
