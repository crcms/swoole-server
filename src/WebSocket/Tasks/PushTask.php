<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Tasks;

use CrCms\Server\Server\Contracts\TaskContract;
use CrCms\Server\WebSocket\Server;
use Illuminate\Contracts\Container\Container;
use OutOfBoundsException;

/**
 * Class PushTask.
 */
final class PushTask implements TaskContract
{
    /**
     * @param mixed ...$params
     *
     * @return mixed|void
     */
    public function handle(...$params): void
    {
        /* @var Server $server */
        $server = array_shift($params);
        /* @var int $fd */
        $fd = array_shift($params);
        /* @var Container $app */
        $app = $server->getApplication();

        $packData = $app->make('websocket.parser')->pack(
            $app->make('websocket.data_converter')->conversion($params)
        );

        if ($server->getServer()->isEstablished($fd)) {
            $server->getServer()->push($fd, $packData);

            return;
        }

        throw new OutOfBoundsException("The fd:[{$fd}] not websocket or websocket close");
    }

    /**
     * @param $data
     *
     * @return mixed|void
     */
    public function finish($data)
    {
    }
}
