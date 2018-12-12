<?php

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
        'websocket' => [
            'driver' => CrCms\Server\WebSocket\Server::class,
            'host' => '0.0.0.0',
            'port' => 28082,
            'mode' => defined('SWOOLE_PROCESS') ? SWOOLE_PROCESS : 3,
            'type' => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,
            'settings' => [
                'task_worker_num' => 2,
                'user' => env('SWOOLE_USER'),
                'group' => env('SWOOLE_GROUP'),
                'log_level' => 4,
                'log_file' => storage_path('logs/websocket.log'),
            ]
        ],

        'http' => [
            'driver' => CrCms\Server\Http\Server::class,
            'host' => '0.0.0.0',
            'port' => 80,
            'mode' => defined('SWOOLE_PROCESS') ? SWOOLE_PROCESS : 3,
            'type' => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,
            'settings' => [
                'user' => env('SWOOLE_USER'),
                'group' => env('SWOOLE_GROUP'),
                'log_level' => 4,
                'log_file' => storage_path('logs/http.log'),
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Register reload provider events
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */
    'reload_provider_events' => [
        \Illuminate\Foundation\Http\Events\RequestHandled::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel reload providers
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */

    'reload_providers' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | ProcessManager file
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */

    'process_file' => storage_path('process.pid'),

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
    'enable_websocket' => true,

    'rooms' => [

        'default' => 'redis',

        'connections' => [
            'redis' => [
                'connection' => 'websocket',
            ]
        ]
    ],

    'websocket_middleware' => [
        \CrCms\Server\WebSocket\Middleware\TestMiddleware::class,
    ]
];