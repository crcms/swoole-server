<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;

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

        IO::dispatch('disconnection', ['fd' => $this->fd]);
    }
}