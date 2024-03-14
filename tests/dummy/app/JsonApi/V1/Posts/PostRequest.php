<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1\Posts;

use App\Models\Post;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PostRequest extends ResourceRequest
{

    /**
     * @return array
     */
    public function rules(): array
    {
        $unique = Rule::unique('posts');

        if ($post = $this->model()) {
            $unique->ignore($post);
        }

        return [
            'content' => ['required', 'string'],
            'deletedAt' => ['nullable', JsonApiRule::dateTime()],
            'media' => JsonApiRule::toMany(),
            'slug' => ['required', 'string', $unique],
            'synopsis' => ['required', 'string'],
            'tags' => JsonApiRule::toMany(),
            'title' => ['required', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function deleteRules(): array
    {
        return [
            'meta.no_comments' => 'accepted',
        ];
    }

    /**
     * @return array
     */
    public function deleteMessages(): array
    {
        return [
            'meta.no_comments.accepted' => 'Cannot delete a post with comments.',
        ];
    }

    /**
     * @param Post $post
     * @return array
     */
    public function metaForDelete(Post $post): array
    {
        return [
            'no_comments' => $post->comments()->doesntExist(),
        ];
    }
}
