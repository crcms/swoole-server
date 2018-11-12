<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class CloseEvent
 * @package CrCms\Server\Server\Events
 */
class CloseEvent extends AbstractEvent implements EventContract
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * @var int
     */
    protected $reactorId;

    /**
     * CloseEvent constructor.
     * @param int $fd
     * @param int $reactorId
     */
    public function __construct(int $fd, int $reactorId)
    {
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);
    }
}