<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Socket;

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

        /* @var $socket Socket */
        $socket = $server->getApplication()->make('websocket');
        $socket->leave();

        $server->getApplication()->instance('websocket', null);

        if (Socket::eventExists('disconnection')) {
            $server->getApplication()->make('websocket')->dispatch('disconnection');
        }
    }
}