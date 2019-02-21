<?php

namespace CrCms\Server\Server;

use function CrCms\Server\clear_opcache;
use function CrCms\Server\set_process_name;

/**
 *
 */
class EventHelper
{
    /**
     * @var AbstractServer
     */
    protected $server;

    /**
     * @param AbstractServer $server
     */
    public function __construct(AbstractServer $server)
    {
        $this->server = $server;
    }

    /**
     * startEvent
     *
     * @return void
     */
    public function startEvent(): void
    {
        set_process_name('master');
    }

    /**
     * managerStart
     *
     * @return void
     */
    public function managerStart(): void
    {
        set_process_name('manager');
    }

    /**
     * workerStart
     *
     * @param int $workerId
     * @return void
     */
    public function workerStart(int $workerId): void
    {
        set_process_name(($this->server->taskworker ?
                'task_' :
                'worker_'
            ).strval($workerId).'_'.$this->server->getConfig()['process_prefix'].'_'.$this->server->name());

        clear_opcache();
    }
}