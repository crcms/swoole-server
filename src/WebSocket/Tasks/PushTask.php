<?php

namespace CrCms\Server\WebSocket\Tasks;

use CrCms\Server\Server\Contracts\TaskContract;
use CrCms\Server\WebSocket\Server;

/**
 * Class PushTask
 * @package CrCms\Server\WebSocket\Tasks
 */
final class PushTask implements TaskContract
{
    /**
     * @param mixed ...$params
     * @return mixed|void
     */
    public function handle(...$params): void
    {
        /* @var Server $server */
        $server = array_shift($params);

        /* @var int $fd */
        $fd = array_shift($params);

        $packData = $server->getApplication()->make('websocket.parser')->pack($params);

        $server->getServer()->push($fd, $packData);
    }

    /**
     * @param $data
     * @return mixed|void
     */
    public function finish($data)
    {
    }
}