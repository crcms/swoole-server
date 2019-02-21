<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Tasks;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\TaskContract;
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
        /* @var AbstractServer $server */
        $server = array_shift($params);
        /* @var int $fd */
        $fd = array_shift($params);
        /* @var array $data */
        $data = $params;

        if ($server->getServer()->isEstablished($fd)) {
            $server->getServer()->push($fd, $data);

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
