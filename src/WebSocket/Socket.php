<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

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

    protected $originalFrame;

    protected $request;

    protected $frame;

    public function __construct(Container $app, Request $request, Channel $channel, Frame $originalFrame, array $frame = [])
    {
        $this->app = $app;
        $this->request = $request;
        $this->channel = $channel;
        $this->originalFrame = $originalFrame;
        $this->frame = $frame;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function getFrame(): array
    {
        return $this->frame;
    }

//    public function setFrame(object $frame): self
//    {
//        $this->frame = $frame;
//        return $this;
//    }

    public function join($room)
    {
        $this->channel->join($this->originalFrame->fd,$room);
    }


    public function getFd(): int
    {
        return $this->originalFrame->fd;
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