<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Contracts\Auth\Authorizer;
use LaravelJsonApi\Contracts\Query\QueryParameters;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\FilterParameters;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\SortFields;
use LaravelJsonApi\Laravel\Exceptions\HttpNotAcceptableException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use function array_key_exists;

class ResourceQuery extends FormRequest implements QueryParameters
{

    /**
     * @var callable|null
     */
    private static $queryManyResolver;

    /**
     * @var callable|null
     */
    private static $queryOneResolver;

    /**
     * The media types the resource accepts, in addition to JSON:API.
     *
     * @var string[]
     */
    protected array $mediaTypes = [];

    /**
     * The include paths to use if the client provides none.
     *
     * @var string[]|null
     */
    protected ?array $defaultIncludePaths = null;

    /**
     * Specify the callback to use to guess the request class for querying many resources.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessQueryManyUsing(callable $resolver): void
    {
        self::$queryManyResolver = $resolver;
    }

    /**
     * Resolve the request instance when querying many resources.
     *
     * @param string $resourceType
     * @return QueryParameters|ResourceQuery
     */
    public static function queryMany(string $resourceType): QueryParameters
    {
        $resolver = self::$queryManyResolver ?: new RequestResolver(RequestResolver::COLLECTION_QUERY);

        return $resolver($resourceType);
    }

    /**
     * Specify the callback to use to guess the request class for querying one resource.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessQueryOneUsing(callable $resolver): void
    {
        self::$queryOneResolver = $resolver;
    }

    /**
     * Resolve the request instance when querying one resource.
     *
     * @param string $resourceType
     * @return QueryParameters|ResourceQuery
     */
    public static function queryOne(string $resourceType): QueryParameters
    {
        $resolver = self::$queryOneResolver ?: new RequestResolver(RequestResolver::QUERY);

        return $resolver($resourceType);
    }

    /**
     * Perform resource authorization.
     *
     * @param Authorizer $authorizer
     * @return bool
     */
    public function authorizeResource(Authorizer $authorizer): bool
    {
        if ($this->isViewingAny()) {
            return $authorizer->index(
                $this,
                $this->schema()->model(),
            );
        }

        if ($this->isViewingOne()) {
            return $authorizer->show($this, $this->modelOrFail());
        }

        if ($this->isViewingRelated()) {
            return $authorizer->showRelated(
                $this,
                $this->modelOrFail(),
                $this->jsonApi()->route()->fieldName(),
            );
        }

        if ($this->isViewingRelationship()) {
            return $authorizer->showRelationship(
                $this,
                $this->modelOrFail(),
                $this->jsonApi()->route()->fieldName(),
            );
        }

        return true;
    }

    /**
     * @return array
     */
    public function validationData()
    {
        return $this->query();
    }

    /**
     * @inheritDoc
     */
    public function includePaths(): ?IncludePaths
    {
        $data = $this->validated();

        if (array_key_exists('include', $data)) {
            return IncludePaths::fromString($data['include'] ?: '');
        }

        return IncludePaths::nullable($this->defaultIncludePaths());
    }

    /**
     * @inheritDoc
     */
    public function sparseFieldSets(): ?FieldSets
    {
        $data = $this->validated();

        if (array_key_exists('fields', $data)) {
            return FieldSets::fromArray($data['fields']);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function sortFields(): ?SortFields
    {
        $data = $this->validated();

        if (array_key_exists('sort', $data)) {
            return SortFields::fromString($data['sort'] ?: '');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function page(): ?array
    {
        $data = $this->validated();

        if (array_key_exists('page', $data)) {
            return $data['page'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function filter(): ?FilterParameters
    {
        $data = $this->validated();

        if (array_key_exists('filter', $data)) {
            return FilterParameters::fromArray($data['filter'] ?? []);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function unrecognisedParameters(): array
    {
        return collect($this->validated())->forget([
            'include',
            'fields',
            'sort',
            'page',
            'filter',
        ])->all();
    }

    /**
     * Get the model that the request relates to, if the URL has a resource id.
     *
     * @return Model|object|null
     */
    protected function model(): ?object
    {
        $route = $this->jsonApi()->route();

        if ($route->hasResourceId()) {
            return $route->model();
        }

        return null;
    }

    /**
     * Get the model that the request relates to, or fail if there is none.
     *
     * @return Model|object
     */
    protected function modelOrFail(): object
    {
        if ($model = $this->model()) {
            return $model;
        }

        throw new LogicException('No model exists for this route.');
    }

    /**
     * Get the default include paths to use if the client has provided none.
     *
     * @return string[]|null
     */
    protected function defaultIncludePaths(): ?array
    {
        return $this->defaultIncludePaths;
    }

    /**
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!$this->isAcceptableMediaType()) {
            throw $this->notAcceptable();
        }
    }

    /**
     * @inheritDoc
     */
    protected function failedValidation(Validator $validator)
    {
        throw new JsonApiException($this->validationErrors()->createErrorsForQuery(
            $validator
        ));
    }

    /**
     * @return bool
     */
    protected function isAcceptableMediaType(): bool
    {
        /**
         * We expect the JSON:API media type to exactly match.
         */
        foreach ($this->getAcceptableContentTypes() as $contentType) {
            if (self::JSON_API_MEDIA_TYPE === $contentType) {
                return true;
            }
        }

        /**
         * Otherwise we check if any additional media types match.
         */
        return $this->accepts($this->mediaTypes());
    }

    /**
     * Get the media types the resource accepts, in addition to JSON:API.
     *
     * @return string[]
     */
    protected function mediaTypes(): array
    {
        return $this->mediaTypes;
    }

    /**
     * Get an exception if the media type is not acceptable.
     *
     * @return HttpExceptionInterface
     */
    protected function notAcceptable(): HttpExceptionInterface
    {
        return new HttpNotAcceptableException();
    }
}
