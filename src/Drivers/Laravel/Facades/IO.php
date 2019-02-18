<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Facades;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Container getApplication()
 * @method static RoomContract getRoom()
 * @method static IO addChannel(Channel $channel)
 * @method static IO setChannel(Channel $channel)
 * @method static Channel of(string $channel)
 * @method static Channel getChannel(string $channel)
 * @method static array getChannels()
 * @method static bool channelExists(string $channel)
 *
 * Class IO
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
