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

namespace LaravelJsonApi\Spec;

use LaravelJsonApi\Spec\Values\Identifier;
use LaravelJsonApi\Spec\Values\ToMany;
use LaravelJsonApi\Spec\Values\ToOne;

class Factory
{

    /**
     * @var Specification
     */
    private Specification $spec;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * Factory constructor.
     *
     * @param Specification $spec
     * @param Translator $translator
     */
    public function __construct(Specification $spec, Translator $translator)
    {
        $this->spec = $spec;
        $this->translator = $translator;
    }


    /**
     * Create a resource identifier object.
     *
     * @param string $path
     * @param $value
     * @return Identifier
     */
    public function createIdentifierValue(string $path, $value): Identifier
    {
        return new Identifier($this->spec, $this->translator, $path, $value);
    }

    /**
     * Create a to-one relationship object.
     *
     * @param string $path
     * @param $value
     * @return ToOne
     */
    public function createToOneValue(string $path, $value): ToOne
    {
        return new ToOne($this->translator, $this, $path, $value);
    }

    /**
     * Create a to-many relationship object.
     *
     * @param string $path
     * @param $value
     * @return ToMany
     */
    public function createToManyValue(string $path, $value): ToMany
    {
        return new ToMany($this->translator, $this, $path, $value);
    }
}
