<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\TaskContract;
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
     * @param int $taskId
     * @param int $workerId
     * @param $data
     */
    public function __construct(AbstractServer $server,int $taskId, int $workerId, $data)
    {
        parent::__construct($server);
        $this->taskId = $taskId;
        $this->workId = $workerId;
        $this->data = $data;
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

            $this->server->getServer()->finish($result);
        } catch (\Throwable $exception) {
            if (method_exists($object, 'failed')) {
                $object->failed($exception);
            }

            throw $exception;
        }
    }
}
