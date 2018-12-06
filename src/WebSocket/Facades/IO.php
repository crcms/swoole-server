<?php

namespace CrCms\Server\WebSocket\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class IO
 * @package CrCms\Server\WebSocket\Facades
 */
class IO extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'websocket.io';
    }
}