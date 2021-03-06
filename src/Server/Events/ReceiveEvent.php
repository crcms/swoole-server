<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class ReceiveEvent.
 */
class ReceiveEvent extends AbstractEvent implements EventContract
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * @var int
     */
    protected $formId;

    /**
     * @var string
     */
    protected $data;

    /**
     * ReceiveEvent constructor.
     *
     * @param int    $fd
     * @param int    $fromId
     * @param string $data
     */
    public function __construct(int $fd, int $fromId, string $data)
    {
        $this->fd = $fd;
        $this->formId = $fromId;
        $this->data = $data;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server) : void
    {
        parent::handle($server); // TODO: Change the autogenerated stub
    }
}
