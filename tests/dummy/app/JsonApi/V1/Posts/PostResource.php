<?php

declare(strict_types=1);

namespace DummyApp\JsonApi\V1\Posts;

use LaravelJsonApi\Core\Resources\JsonApiResource;
use function url;

class PostResource extends JsonApiResource
{

    /**
     * @return string
     */
    public function type(): string
    {
        return 'posts';
    }

    /**
     * @return string
     */
    public function selfUrl(): string
    {
        return url('api/v1', [$this->type(), $this->id()]);
    }

    /**
     * @return iterable
     */
    public function attributes(): iterable
    {
        return [
            'content' => $this->content,
            'createdAt' => $this->created_at,
            'synopsis' => $this->synopsis,
            'title' => $this->title,
            'updatedAt' => $this->updated_at,
        ];
    }

    /**
     * @return iterable
     */
    public function relationships(): iterable
    {
        return [
            'author' => $this->relation('author'),
        ];
    }

}
