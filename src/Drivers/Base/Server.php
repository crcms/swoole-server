<?php

namespace CrCms\Server\Drivers\Base;

use CrCms\Server\Drivers\Base\Events\RequestEvent;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\ServerFactory;
use Swoole\Server as SwooleServer;

class Server extends AbstractServer
{
    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->events['request'] = RequestEvent::class;
        parent::__construct($config);
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

    public function create(): SwooleServer
    {
        return ServerFactory::factory('http', $this->baseConfig);
    }


}