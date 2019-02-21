<?php

namespace CrCms\Server\Server\Tasks;

use CrCms\Server\Server\Contracts\TaskContract;
use Swoole\Server;

/**
 * Class Dispatcher.
 */
class Dispatcher
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * Dispatcher constructor.
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * dispatch
     *
     * @param TaskContract $task
     * @param array $params
     * @param bool $async
     * @param float $timeout
     * @return false|int|string
     */
    public function dispatch(TaskContract $task, array $params = [], bool $async = true, float $timeout = 1)
    {
        $data = ['object' => $task, 'params' => $params];

        return $async ? $this->server->task($data, -1, function (Server $server, int $taskId, $data) use ($task) {
            $task->finish($data);
        }) : $this->server->taskwait($data, $timeout);
    }
}
