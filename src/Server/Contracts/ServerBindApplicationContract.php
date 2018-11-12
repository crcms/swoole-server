<?php

namespace CrCms\Server\Server\Contracts;

/**
 * Interface ServerBindApplicationContract
 * @package CrCms\Server\Server\Contracts
 */
interface ServerBindApplicationContract
{
    /**
     * @param ServerContract $server
     * @return void
     */
    public function bindServer(ServerContract $server): void;

    /**
     * @return ServerContract
     */
    public function getServer(): ServerContract;
}