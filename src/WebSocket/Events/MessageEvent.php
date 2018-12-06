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
    protected $frame;

    public function __construct($frame)
    {
        $this->frame = $frame;
    }

    public function handle(AbstractServer $server): void
    {
        parent::handle($server); // TODO: Change the autogenerated stub

        $app = $this->server->getApplication();

        IO::dispatch('message', ['frame' => $this->frame]);
    }
}