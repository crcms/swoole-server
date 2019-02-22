<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-02-22 21:56
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Drivers\Laravel\Http\Server;
use Illuminate\Contracts\Container\Container;

class WorkerStartedEvent
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @param Server $server
     */
    public function __construct(Server $server, Container $app)
    {
        $this->server = $server;
        $this->app = $app;
    }
}