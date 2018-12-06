<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class Socket
 * @package CrCms\Server\WebSocket
 */
class Socket
{
    use EventConcern;

    protected static $eventPrefix = 'socket';

    public $channel;

    protected $app;

    protected $frame;

    protected $fd;

    public function __construct(Application $app, Channel $channel, int $fd)
    {
        $this->app = $app;
        $this->channel = $channel;
        //$this->frame = $frame;
        $this->fd = $fd;
    }

    public function setFrame(object $frame): self
    {
        $this->frame = $frame;
        return $this;
    }

    public function getFrame(): object
    {
        return $this->frame;
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    public function broadcast()
    {
        return $this->channel;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function emit(string $event, array $data = [])
    {
        //调用一个task，或者直接push message
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->channel, $name)) {
            array_unshift($arguments, $this);
            return $this->channel->$name(...$arguments);
        }

        throw new \BadMethodCallException("The method[{$name}] not found");
    }
}