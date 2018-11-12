<?php

namespace CrCms\Server\Server\Tasks;

use CrCms\Server\Server\Contracts\TaskContract;
use CrCms\Server\Server\AbstractServer;
use Swoole\Server;

/**
 * Class Dispatcher
 * @package CrCms\Server\Server\Tasks
 */
class Dispatcher
{
    /**
     * @param TaskContract $task
     * @param array $params
     * @param bool $async
     * @return false|int|string
     */
    public static function dispatch(TaskContract $task, array $params = [], bool $async = true)
    {
        /* @var AbstractServer|Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server */
        $server = app()->getServerApplication()->getServer();

        $data = ['object' => $task, 'params' => $params];

        return $async ? $server->task($data, -1, function (Server $server, int $taskId, $data) use ($task) {
            $task->finish($data);
        }) : $server->taskwait($data);
    }
}