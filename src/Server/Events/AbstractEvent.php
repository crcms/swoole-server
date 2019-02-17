<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class AbstractEvent.
 */
abstract class AbstractEvent
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

    abstract public function handle(): void;

    /**
     * @return AbstractServer
     */
    public function getServer(): AbstractServer
    {
        return $this->server;
    }

    /**
     * setEventProcessName
     *
     * @param string $processName
     * @return void
     */
    protected function setEventProcessName(string $processName): void
    {
        set_process_name($this->server->getConfig()['process_prefix'].'_'.$this->server->name());
    }
}
