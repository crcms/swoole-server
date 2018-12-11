<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use Swoole\Http\Request as SwooleRequest;

/**
 * Class OpenEvent
 * @package CrCms\Server\WebSocket\Events
 */
class OpenEvent extends AbstractEvent
{
    /**
     * @var SwooleRequest
     */
    protected $request;

    /**
     * OpenEvent constructor.
     * @param SwooleRequest $request
     */
    public function __construct(SwooleRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        IO::dispatch('connection', ['app' => $this->server->getApplication(), 'request' => $this->getRequest()]);
    }

    /**
     * @return SwooleRequest
     */
    public function getRequest(): SwooleRequest
    {
        return $this->request;
    }
}