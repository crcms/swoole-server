<?php

namespace CrCms\Server\Server\Contracts;

use CrCms\Server\Server\AbstractServer;

interface EventContract
{
    /**
     * @return void
     */
    public function handle(): void;
}
