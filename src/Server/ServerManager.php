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
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->checkProcessExists()) {
            return $this->server->newServer()->start();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        if (!$this->checkProcessExists()) {
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
        if ($this->checkProcessExists()) {
            $this->stop();
            sleep(2);
        }

        return $this->start();
    }

    public function reload()
    {
        if (!$this->checkProcessExists()) {
            throw new RuntimeException('The process not exists');
        }

        if (Process::kill($this->getPid(),SIGUSR1)) {
            return true;
        }
    }

    /**
     * @return int
     */
    protected function getPid(): int
    {
        $pidFile = $this->server->getSettings()['pid_file'];
        if (!file_exists($pidFile)) {
            return -99999;
        }

        return (int) file_get_contents($pidFile);
    }

    /**
     * @return bool
     */
    protected function checkProcessExists(): bool
    {
        return Process::kill($this->getPid(), 0);
    }
}
