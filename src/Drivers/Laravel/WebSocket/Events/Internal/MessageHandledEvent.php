<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Events\Internal;

use CrCms\Server\WebSocket\Socket;

/**
 * Class MessageHandledEvent.
 */
class MessageHandledEvent
{
    /**
     * @var Socket
     */
    public $socket;

    /**
     * MessageHandledEvent constructor.
     *
     * @param Socket $socket
     */
    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }
}
