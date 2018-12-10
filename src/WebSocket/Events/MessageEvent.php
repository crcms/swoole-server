<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\WebSocket\Socket;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;

/**
 * Class MessageEvent
 * @package CrCms\Framework\Http\Events
 */
class MessageEvent extends AbstractEvent implements EventContract
{
    /**
     * @var object
     */
    protected $frame;

    /**
     * MessageEvent constructor.
     * @param $frame
     */
    public function __construct($frame)
    {
        $this->frame = $frame;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        IO::dispatch('message', ['app' => $server->getApplication(), 'frame' => $this->frame, 'request' => $server->request]);
    }
}