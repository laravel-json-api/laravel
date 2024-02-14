<?php
/*
 * Copyright 2024 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\GeneratorCommand as BaseGeneratorCommand;
use LaravelJsonApi\Core\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use function config;
use function is_string;

abstract class GeneratorCommand extends BaseGeneratorCommand
{

    use Concerns\ResolvesStub;

    /**
     * @var string
     */
    protected $classType;

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $server = $this->getServerInput();

        if ($this->doesRequireServer() && empty($server)) {
            $this->error('You must use the server option when you have more than one API.');
            return 1;
        }

        if (!empty($server) && is_null($this->getServerNamespace($server))) {
            $this->error("Server {$server} does not exist in your jsonapi.servers configuration.");
            return 1;
        }

        if (false === parent::handle()) {
            return 1;
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    protected function getNameInput()
    {
        $name = parent::getNameInput();

        return Str::classify(Str::singular($name)) . $this->getClassType();
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $name = parent::getNameInput();

        $namespace = Str::classify(Str::plural($name));

        return $this->getServerNamespace($this->getServerInput()) . '/' . $namespace;
    }

    /**
     * Get the server input.
     *
     * The developer can provide a server name using the `--server`
     * option. If they do not provide it, we expect them to have a
     * single server in their `jsonapi` config.
     *
     * @return string|null
     */
    protected function getServerInput(): ?string
    {
        if ($server = $this->option('server')) {
            return $server;
        }

        $servers = config('jsonapi.servers') ?: [];

        if (1 === count($servers)) {
            return array_key_first($servers);
        }

        return null;
    }

    /**
     * @param string $server
     * @return string|null
     */
    protected function getServerNamespace(string $server): ?string
    {
        $classname = config("jsonapi.servers.{$server}");

        if (is_string($classname)) {
            return $this->getNamespace($classname);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function guessModel(): string
    {
        $name = parent::getNameInput();

        return Str::classify(Str::singular($name));
    }

    /**
     * @return string
     */
    protected function getClassType(): string
    {
        return $this->classType;
    }

    /**
     * Does the generator require a server to be specified?
     *
     * Child classes can overload this method if a server is not required.
     *
     * @return bool
     */
    protected function doesRequireServer(): bool
    {
        return true;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the JSON:API resource type.'],
        ];
    }

}
