<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class WorkerStartEvent.
 */
class WorkerStartEvent extends AbstractEvent implements EventContract
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
    public function __construct(int $workId)
    {
        $this->workId = $workId;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        parent::setEventProcessName(($this->server->taskworker ?
                'task_' :
                'worker_'
            ).strval($this->workId));
    }
}
