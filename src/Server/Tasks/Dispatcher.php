<?php

namespace CrCms\Server\Server\Tasks;

use CrCms\Server\Server\Contracts\TaskContract;
use CrCms\Server\Server\AbstractServer;
use Illuminate\Contracts\Container\Container;
use Swoole\Server;

/**
 * Class Dispatcher
 * @package CrCms\Server\Server\Tasks
 */
class Dispatcher
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * Dispatcher constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param TaskContract $task
     * @param array $params
     * @param bool $async
     * @return false|int|string
     */
    public function dispatch(TaskContract $task, array $params = [], bool $async = true)
    {
        $data = ['object' => $task, 'params' => $params];

        return $async ? $this->server->task($data, -1, function (Server $server, int $taskId, $data) use ($task) {
            $task->finish($data);
        }) : $this->server->taskwait($data);
    }
}