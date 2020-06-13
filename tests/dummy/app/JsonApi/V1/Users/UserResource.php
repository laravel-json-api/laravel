<?php

declare(strict_types=1);

namespace DummyApp\JsonApi\V1\Users;

use LaravelJsonApi\Core\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{

    /**
     * @return string
     */
    public function type(): string
    {
        return 'users';
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
            'createdAt' => $this->created_at,
            'name' => $this->name,
            'updatedAt' => $this->updated_at,
        ];
    }

    /**
     * @return iterable
     */
    public function relationships(): iterable
    {
        return [];
    }


}
