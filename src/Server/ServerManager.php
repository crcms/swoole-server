<?php

namespace CrCms\Server\Server;

use CrCms\Server\Server\Contracts\ServerContract;
use Illuminate\Console\Command;
use CrCms\Server\Server;
use Swoole\Process;
use Throwable;
use RuntimeException;
use CrCms\Server\Process\ProcessManager;

/**
 * Class ServerManager
 * @package CrCms\Server\Server
 */
class ServerManager implements Server\Contracts\ServerStartContract, Server\Contracts\ServerActionContract
{
    /**
     * @var array
     */
    protected $allows = ['start', 'stop', 'restart'];//, 'reload'

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var ServerContract
     */
    protected $server;

    /**
     * @var ProcessManager
     */
    protected $process;

    /**
     * @param Command $command
     * @param ServerContract $server
     * @param ProcessManager $process
     */
    public function run(Command $command, ServerContract $server, ProcessManager $process): void
    {
        $this->command = $command;
        $this->server = $server;
        $this->process = $process;

        $action = $command->argument('action');

        if (in_array($action, $this->allows, true)) {
            try {
                $result = call_user_func([$this, $action]);
                if ($action === 'start') return;
                if ($result === false) {
                    $command->getOutput()->error("{$action} failed");
                } else {
                    $command->getOutput()->success("{$action} successfully");
                }
            } catch (Throwable $exception) {
                $command->getOutput()->error($exception->getMessage());
            }
        } else {
            $command->getOutput()->error("Allow only " . implode($this->allows, ' ') . "options");
        }
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->checkProcessExists()) {
            $this->server->createServer();
            $this->server->bootstrap();

            // д��ǰ�棬��Ϊswoole �� start֮��Ͳ���ִ�к���Ĵ�����
            // ������쳣Ҳ���׳�����ִ�д˷���
            $this->command->getOutput()->success("start successfully");

            return $this->server->start();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        if (!$this->checkProcessExists()) {
            throw new RuntimeException("The process not exists");
        }

        if (Process::kill($this->getPid())) {
            @unlink($this->getPidFile());
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

    /**
     * @return string
     */
    protected function getPidFile(): string
    {
        return $this->server->pidFile();
    }

    /**
     * @return int
     */
    protected function getPid(): int
    {
        $pidFile = $this->getPidFile();
        if (!file_exists($pidFile)) {
            return -99999;
        }

        return (int)file_get_contents($pidFile);
    }

    /**
     * @return bool
     */
    protected function checkProcessExists(): bool
    {
        return Process::kill($this->getPid(), 0);
    }
}