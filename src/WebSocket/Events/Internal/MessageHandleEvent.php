<?php

namespace CrCms\Server\WebSocket\Events\Internal;

use CrCms\Server\WebSocket\Socket;

/**
 * Class MessageHandleEvent
 * @package CrCms\Server\WebSocket\Events\Internal
 */
class MessageHandleEvent
{
    /**
     * @var Socket
     */
    public $socket;

    /**
     * MessageHandledEvent constructor.
     * @param Socket $socket
     */
    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }
}