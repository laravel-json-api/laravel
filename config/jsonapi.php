<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    |
    | A list of the JSON:API compliant APIs in your application, referred to
    | as "servers". They must be listed below, with the array key being the
    | unique name for each server, and the value being the fully-qualified
    | class name of the server class.
    */
    'servers' => [
        'v1' => \App\JsonApi\V1\Server::class,
    ],
];
