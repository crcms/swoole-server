<?php

namespace CrCms\Server\Server\Contracts;

use CrCms\Server\Process\ProcessManager;
use Illuminate\Console\Command;

/**
 * Interface ServerStartContract.
 */
interface ServerStartContract
{
    /**
     * @param Command        $command
     * @param ServerContract $server
     * @param ProcessManager $process
     */
    public function run(Command $command, ServerContract $server, ProcessManager $process): void;
}
