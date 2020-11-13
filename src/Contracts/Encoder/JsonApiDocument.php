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

namespace LaravelJsonApi\Contracts\Encoder;

use LaravelJsonApi\Contracts\Serializable;

interface JsonApiDocument extends Serializable
{

    /**
     * Set the top-level JSON API member.
     *
     * @param $jsonApi
     * @return $this
     */
    public function withJsonApi($jsonApi): self;

    /**
     * Set the top-level links member.
     *
     * @param $links
     * @return $this
     */
    public function withLinks($links): self;

    /**
     * Set the top-level meta member.
     *
     * @param $meta
     * @return $this
     */
    public function withMeta($meta): self;

}
