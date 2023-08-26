<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

    public function validateResolved()
    {
        // no-op
    }
}
