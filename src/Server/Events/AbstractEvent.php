<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class AbstractEvent.
 */
abstract class AbstractEvent implements EventContract
{
    /**
     * @var AbstractServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param AbstractServer $server
     * @return void
     */
    public function run(AbstractServer $server, array $config)
    {
        $this->server = $server;
        $this->config = $config;

        $this->handle();
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
        $processPrefix = config('swoole.process_prefix', 'swoole').($this->server->getName() ? '_'.$this->server->getName() : '');

        $processName = ($processPrefix ? $processPrefix.'_' : $processPrefix).$processName;

        static::setProcessName($processName);
    }

    /**
     * @param string $name
     *
     * @return bool|void
     */
    protected static function setProcessName(string $name)
    {
        if (function_exists('cli_set_process_title')) {
            return cli_set_process_title($name);
        } elseif (function_exists('swoole_set_process_name')) {
            return swoole_set_process_name($name);
        }
    }
}
