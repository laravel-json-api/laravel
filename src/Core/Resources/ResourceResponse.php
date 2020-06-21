<?php

namespace LaravelJsonApi\Core\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Http\Server;

class ResourceResponse implements Responsable
{

    /**
     * @var JsonApiResource|null
     */
    private $resource;

    /**
     * @var bool
     */
    private $created = false;

    /**
     * @var Hash
     */
    private $meta;

    /**
     * @var Links
     */
    private $links;

    /**
     * @var int
     */
    private $encodeOptions;

    /**
     * ResourceResponse constructor.
     *
     * @param JsonApiResource|null $resource
     */
    public function __construct(?JsonApiResource $resource)
    {
        $this->resource = $resource;
        $this->meta = new Hash();
        $this->links = new Links();
        $this->encodeOptions = 0;
    }

    /**
     * Add top-level meta to the response.
     *
     * @param $meta
     * @return $this
     */
    public function withMeta($meta): self
    {
        $this->meta = Hash::cast($meta);

        return $this;
    }

    /**
     * Add top-level links to the response.
     *
     * @param $links
     * @return $this
     */
    public function withLinks($links): self
    {
        $this->links = Links::cast($links);

        return $this;
    }

    /**
     * Set the JSON encode options.
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
     * @param Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        /** @var Server $server */
        $server = app(Server::class);

        $document = $server->encoder()
            ->withIncludePaths($this->includePaths($request))
            ->withFieldSets($this->fieldSets($request))
            ->withResource($this->resource)
            ->withMeta($this->meta)
            ->withLinks($this->links)
            ->toJson($this->encodeOptions);

        return response(
            $document,
            $this->didCreate() ? 201 : 200,
            $this->headers()
        );
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        $headers = ['Content-Type' => 'application/vnd.api+json'];

        if ($this->didCreate()) {
            $headers['Location'] = $this->resource->selfUrl();
        }

        return $headers;
    }

    /**
     * @param Request $request
     * @return IncludePaths
     */
    protected function includePaths($request): IncludePaths
    {
        if ($include = $request->query('include')) {
            return IncludePaths::fromString($include);
        }

        return new IncludePaths();
    }

    /**
     * @param Request $request
     * @return FieldSets
     */
    protected function fieldSets($request): FieldSets
    {
        if ($fieldSets = $request->query('fields')) {
            return FieldSets::fromArray($fieldSets);
        }

        return new FieldSets();
    }

    /**
     * @return bool
     */
    protected function didCreate(): bool
    {
        if ($this->resource->resource instanceof Model) {
            return $this->resource->resource->wasRecentlyCreated;
        }

        return $this->created;
    }

}
