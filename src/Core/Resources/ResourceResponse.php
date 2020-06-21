<?php

namespace LaravelJsonApi\Core\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Http\Server;

class ResourceResponse implements Responsable
{

    use Concerns\CreatesResponse;

    /**
     * @var JsonApiResource|null
     */
    private $resource;

    /**
     * @var bool
     */
    private $created = false;

    /**
     * ResourceResponse constructor.
     *
     * @param JsonApiResource|null $resource
     */
    public function __construct(?JsonApiResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Mark the resource as created.
     *
     * @return $this
     */
    public function didCreate(): self
    {
        $this->created = true;

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
            $this->status(),
            $this->headers()
        );
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        $headers = \collect(['Content-Type' => 'application/vnd.api+json'])
            ->merge($this->headers ?: [])
            ->all();

        if ($this->resourceWasCreated()) {
            $headers['Location'] = $this->resource->selfUrl();
        }

        return $headers;
    }

    /**
     * @return int
     */
    protected function status(): int
    {
        if ($this->resourceWasCreated()) {
            return Response::HTTP_CREATED;
        }

        return Response::HTTP_OK;
    }

    /**
     * @return bool
     */
    protected function resourceWasCreated(): bool
    {
        if (true === $this->created) {
            return true;
        }

        if ($this->resource->resource instanceof Model) {
            return $this->resource->resource->wasRecentlyCreated;
        }

        return false;
    }

}
