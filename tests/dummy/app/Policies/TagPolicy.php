<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TagPolicy
{

    /**
     * Determine if the user can delete the tag
     *
     * @param ?User $user
     * @param Tag $tag
     * @return bool|Response
     */
    public function delete(?User $user, Tag $tag)
    {
        return Response::denyAsNotFound('not found message');
    }
}
