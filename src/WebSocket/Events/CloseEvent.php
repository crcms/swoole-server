<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;

/**
 * Class CloseEvent
 * @package CrCms\Server\WebSocket\Events
 */
class CloseEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * CloseEvent constructor.
     * @param $fd
     */
    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);
        
        /* @var Container $app */
        $app = $server->getApplication();
        /* @var Socket $websocket */
        $websocket = $app->make('websocket');

        if ($websocket->getChannel()->eventExists('disconnection')) {
            $websocket->dispatch('disconnection');
        }

        $websocket->leave();
    }
}