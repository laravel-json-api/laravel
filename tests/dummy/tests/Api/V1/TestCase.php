<?php

declare(strict_types=1);

namespace DummyApp\Tests\Api\V1;

use DummyApp\Tests\TestCase as BaseTestCase;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

class TestCase extends BaseTestCase
{

    use MakesJsonApiRequests;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new Serializer();
    }
}
