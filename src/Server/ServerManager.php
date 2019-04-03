<?php

namespace CrCms\Server\Server;

use RuntimeException;
use Swoole\Process;

/**
 * Class ServerManager.
 */
class ServerManager
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
     * Return current server
     *
     * @return AbstractServer
     */
    public function getServer(): AbstractServer
    {
        return $this->server;
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->processExists()) {
            return $this->server->newServer()->start();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        if (!$this->processExists()) {
            throw new RuntimeException('The process not exists');
        }

        if (Process::kill($this->getPid())) {
            @unlink($this->server->getSettings()['pid_file']);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function restart(): bool
    {
        if ($this->processExists()) {
            $this->stop();
            sleep(2);
        }

        return $this->start();
    }

    /**
     * Reload server
     *
     * @return bool
     */
    public function reload(): bool
    {
        if (!$this->processExists()) {
            throw new RuntimeException('The process not exists');
        }

        return Process::kill($this->getPid(),SIGUSR1);
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        $pidFile = $this->server->getSettings()['pid_file'];
        if (!file_exists($pidFile)) {
            return 1000000;
        }

        return (int) file_get_contents($pidFile);
    }

    /**
     * @return bool
     */
    public function processExists(): bool
    {
        return Process::kill($this->getPid(), 0);
    }
}
