<?php

namespace CrCms\Server\Drivers\Base\Events;


use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use Swoole\Http\Request;
use Swoole\Http\Response;

class RequestEvent extends AbstractEvent
{
    /**
     * @var Request
     */
    protected $swooleRequest;

    /**
     * @var Response
     */
    protected $swooleResponse;

    public function __construct(AbstractServer $server,Request $request,Response $response)
    {
        parent::__construct($server);
        $this->swooleRequest = $request;
        $this->swooleResponse = $response;
    }

    public function handle(): void
    {
        dump($this->swooleRequest);
        $this->swooleResponse->end('abc');
    }
}