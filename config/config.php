<?php

use CrCms\Server\Drivers\Laravel\Resetters;

return [
    /*
    |--------------------------------------------------------------------------
    | Swoole servers
    |--------------------------------------------------------------------------
    |
    | All swoole server collections
    | *.settings.log_level: 0 =>DEBUG 1 =>TRACE 2 =>INFO 3 =>NOTICE 4 =>WARNING 5 =>ERROR
    |
    */

    'servers' => [
        /*'websocket' => [
            'driver'   => CrCms\Server\WebSocket\Server::class,
            'host'     => '0.0.0.0',
            'port'     => 28082,
            'settings' => [
                'task_worker_num' => 2,
                'user'            => env('SWOOLE_USER'),
                'group'           => env('SWOOLE_GROUP'),
                'log_level'       => 4,
                //'log_file'        => storage_path('logs/websocket.log'),
            ],
        ],*/

        'laravel_http' => [
            'driver' => \CrCms\Server\Drivers\Laravel\Http\Server::class,
            'host' => '0.0.0.0',
            'port' => 28082,
            'settings' => [
                'user' => env('SWOOLE_USER'),
                'group' => env('SWOOLE_GROUP'),
                'log_level' => 4,
                'log_file' => '/var/log/laravel_http.log',
            ],
        ],

        'base_http' => [
            'driver' => \CrCms\Server\Drivers\Base\Server::class,
            'host' => '0.0.0.0',
            'port' => 28081,
            'settings' => [
                'user' => env('SWOOLE_USER'),
                'group' => env('SWOOLE_GROUP'),
                'log_level' => 4,
                'log_file' => '/var/log/base_http.log',
            ],
        ],
    ],

    'laravel' => [

        /*
        |--------------------------------------------------------------------------
        | Laravel initialize application
        |--------------------------------------------------------------------------
        |
        | Must be realized CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract
        |
        */

        'app' => \CrCms\Server\Drivers\Laravel\Application::class,

        /*
        |--------------------------------------------------------------------------
        | Laravel preload instance
        |--------------------------------------------------------------------------
        |
        | Load the parsed instance ahead of time
        | This parsing will be an instance of all request sharing for the current worker.
        |
        */

        'preload' => [
            'cache', 'cache.store', 'encrypter', 'db', 'files', 'filesystem', 'hash', 'translator', 'log', 'validator', 'queue',
        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel reload providers
        |--------------------------------------------------------------------------
        |
        | Information file for saving all running processes
        |
        */

        'providers' => [

        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel resetters
        |--------------------------------------------------------------------------
        |
        | Every time you need to load an object that needs to be reset
        | Please note the order of execution of the load
        |
        */

        'resetters' => [
            Resetters\ConfigResetter::class,
            Resetters\ProviderResetter::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel events
        |--------------------------------------------------------------------------
        |
        | Available events
        | start: onStart
        | worker_start: onWorkerStart
        | request: onRequest
        |
        */

        'events' => [
        ],


        /*'websocket_rooms' => [

            'default' => 'redis',

            'connections' => [
                'redis' => [
                    'connection' => 'websocket',
                ],
            ],
        ],*/

        'websocket_channels' => [
            '/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ProcessManager file
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */

    'process_file' => '/var/process.pid',

    /*
    |--------------------------------------------------------------------------
    | Swoole Process Prefix
    |--------------------------------------------------------------------------
    |
    | Server process name prefix
    |
    */

    'process_prefix' => 'swoole',

    /*
    |--------------------------------------------------------------------------
    | Enable websocket
    |--------------------------------------------------------------------------
    |
    */
    'enable_websocket' => false,


    'websocket_parser' => CrCms\Server\WebSocket\Parsers\DefaultParser::class,

    'websocket_data_converter' => CrCms\Server\WebSocket\Converters\DefaultConverter::class,

    'websocket_request_middleware' => [
    ],
];
