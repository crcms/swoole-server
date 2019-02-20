<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\TaskContract;
use Swoole\Server\Task;
use Exception;

/**
 * Class TaskEvent.
 */
class TaskEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $taskId;

    /**
     * @var int
     */
    protected $workId;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param AbstractServer $server
     * @param int|Task $taskId
     * @param int $workerId
     * @param $data
     */
    public function __construct(AbstractServer $server, $taskId, $workerId = -1, $data = null)
    {
        parent::__construct($server);
        $this->parseCoroutineTask($taskId, $workerId, $data);
    }

    /**
     * handle kernel
     *
     * @throws Exception
     */
    public function handle(): void
    {
        /* @var TaskContract $object */
        $object = $this->data['object'];
        /* @var array $params */
        $params = $this->data['params'];

        try {
            $result = $object->handle($this->server, ...$params);

            $this->server->finish($result);
        } catch (\Throwable $exception) {
            if (method_exists($object, 'failed')) {
                $object->failed($exception);
            }

            throw $exception;
        }
    }

    /**
     * parseCoroutineTask
     *
     * @param int|Task $task
     * @param int $workerId
     * @param null $data
     * @return void
     */
    protected function parseCoroutineTask($task, $workerId = -1, $data = null)
    {
        if ($task instanceof Task) {
            $this->taskId = $task->id;
            $this->workId = $task->workerId;
            $this->data = $task->data;
        } else {
            $this->taskId = $task;
            $this->workId = $workerId;
            $this->data = $data;
        }
    }
}
