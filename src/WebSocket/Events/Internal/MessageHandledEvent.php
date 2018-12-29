<?php

namespace CrCms\Server\WebSocket\Events\Internal;

use CrCms\Server\WebSocket\Socket;

/**
 * Class MessageHandledEvent
 * @package CrCms\Server\WebSocket\Events\Internal
 */
class MessageHandledEvent
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