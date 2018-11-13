<?php

namespace CrCms\Server;

use CrCms\Server\Process\ProcessManager;
use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Server\Server\ServerManager;
use Illuminate\Console\Command;

abstract class AbstractServerCommand extends Command
{
    /**
     * @return void
     */
    public function handle(): void
    {
        (new ServerManager)->run(
            $this,
            $this->server(),
            new ProcessManager(config('swoole.process_file'))
        );
    }

    /**
     * @return ServerContract
     */
    abstract public function server(): ServerContract;
}