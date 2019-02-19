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

    /**
     * @param AbstractServer $server
     * @param Request $request
     * @param Response $response
     */
    public function __construct(AbstractServer $server, Request $request, Response $response)
    {
        parent::__construct($server);
        $this->swooleRequest = $request;
        $this->swooleResponse = $response;
    }

    /**
     * handle
     *
     * @return void
     */
    public function handle(): void
    {
    }
}