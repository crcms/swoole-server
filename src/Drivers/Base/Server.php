<?php

namespace CrCms\Server\Drivers\Base;

use CrCms\Server\Drivers\Base\Events\RequestEvent;
use CrCms\Server\Server\AbstractServer;

class Server extends AbstractServer
{
    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->events['request'] =  RequestEvent::class;
    }

    /**
     * name
     *
     * @return string
     */
    public function name(): string
    {
        return 'base.http';
    }
}