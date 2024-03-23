<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\GeneratorCommand as BaseGeneratorCommand;
use LaravelJsonApi\Core\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeServer extends BaseGeneratorCommand
{

    use Concerns\ResolvesStub;

    /**
     * @var string
     */
    protected $name = 'jsonapi:server';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API server.';

    /**
     * @var string
     */
    protected $type = 'JSON:API server';

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $name = parent::getNameInput();

        if (!empty(config("jsonapi.servers.{$name}")) && !$this->option('force')) {
            $this->error("Server {$name} is already registered in your JSON:API configuration.");
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
    protected function getStub()
    {
        return $this->resolveStubPath('server.stub');
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return 'Server';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $jsonApi = trim(config('jsonapi.namespace') ?: 'JsonApi', '\\');
        $name = Str::classify(parent::getNameInput());

        return $rootNamespace . '\\' . $jsonApi . '\\' . $name;
    }

    /**
     * @inheritDoc
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $uri = $this->option('uri') ?: $this->guessUri();

        return $this->replaceUri($stub, $uri);
    }

    /**
     * @param string $stub
     * @param string $uri
     * @return string
     */
    protected function replaceUri(string $stub, string $uri): string
    {
        $uri = rtrim($uri, '/');

        $replace = [
            '{{ uri }}' => $uri,
            '{{uri}}' => $uri,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the server already exists'],
            ['uri', null, InputOption::VALUE_REQUIRED, 'The base URI of the server.'],
        ];
    }

    /**
     * @return string
     */
    private function guessUri(): string
    {
        $name = parent::getNameInput();

        return '/api/' . Str::dasherize($name);
    }
}
