<?php

namespace CrCms\Server\Server\Contracts;

interface EventContract
{
    /**
     * @return void
     */
    public function dispatch(): void;
}
