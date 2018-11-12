<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Concerns\ProcessNameConcern;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class AbstractEvent
 * @package CrCms\Server\Server\Events
 */
abstract class AbstractEvent implements EventContract
{
    use ProcessNameConcern;

    /**
     * @var AbstractServer
     */
    protected $server;

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        $this->server = $server;
    }

    /**
     * @return AbstractServer
     */
    public function getServer(): AbstractServer
    {
        return $this->server;
    }

    /**
     * @param string $processName
     */
    protected function setEventProcessName(string $processName)
    {
        $processPrefix = config('swoole.process_prefix', 'swoole') . ($this->server->getName() ? '_' . $this->server->getName() : '');

        $processName = ($processPrefix ? $processPrefix . '_' : $processPrefix) . $processName;

        static::setProcessName($processName);
    }
}