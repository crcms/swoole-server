<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;

class WorkerStartEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $workId;

    /**
     * @param AbstractServer $server
     * @param int $workId
     */
    public function __construct(AbstractServer $server, int $workId)
    {
        parent::__construct($server);
        $this->workId = $workId;
    }

    /**
     * handle kernel
     *
     * @return void
     */
    public function handle(): void
    {
        parent::setEventProcessName(($this->server->taskworker ?
                'task_' :
                'worker_'
            ).strval($this->workId));
    }
}
