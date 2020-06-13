<?php

declare(strict_types=1);

namespace LaravelJsonApi\Http;

use LaravelJsonApi\Encoder\Encoder;
use LaravelJsonApi\Encoder\Factory as EncoderFactory;

class Server
{

    /**
     * @var string
     */
    private $name;

    /**
     * Server constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Encoder
     */
    public function encoder(): Encoder
    {
        return app(EncoderFactory::class)->build(
            $this->get('resources') ?: []
        );
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    private function get(string $key, $default = null)
    {
        return config("json-api.servers.{$this->name}.{$key}", $default);
    }
}
