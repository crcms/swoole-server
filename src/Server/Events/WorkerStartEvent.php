<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;

/**
 * Class WorkerStartEvent.
 */
class WorkerStartEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $workId;

    /**
     * WorkerStartEvent constructor.
     *
     * @param int $workId
     */
    public function __construct(AbstractServer $server,int $workId)
    {
        parent::__construct($server);
        $this->workId = $workId;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(): void
    {
        parent::setEventProcessName(($this->server->taskworker ?
                'task_' :
                'worker_'
            ).strval($this->workId));
    }
}
