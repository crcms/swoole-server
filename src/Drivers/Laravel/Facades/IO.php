<?php

namespace CrCms\Server\Drivers\Laravel\Facades;

use CrCms\Server\WebSocket\AbstractChannel;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Container getApplication()
 * @method static RoomContract getRoom()
 * @method static IO addChannel(AbstractChannel $channel)
 * @method static IO setChannel(AbstractChannel $channel)
 * @method static AbstractChannel of(string $channel)
 * @method static AbstractChannel getChannel(string $channel)
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
